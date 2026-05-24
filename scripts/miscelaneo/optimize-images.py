#!/usr/bin/env python3
"""
WordPress Image Optimizer for avesnativaschilenas.cl
- Analiza todos los media items
- Redimensiona imagenes >1200px y convierte JPG/PNG a WebP
- Sube las nuevas versiones WebP
- Actualiza posts: URLs en contenido y featured images
"""
import requests, os, io, re, time, json, sys, signal
from PIL import Image
from requests.auth import HTTPBasicAuth
from urllib.parse import urlparse, unquote
from datetime import datetime

# Config
WP_URL = "https://avesnativaschilenas.cl"
USER = "cvieille"
APP_PASSWORD = "u0wM 1VRi v9wL Z71R 7XCx mnEq"
MAX_WIDTH = 1200
MAX_HEIGHT = 1200
WEBP_QUALITY = 75
BATCH_SIZE = 100
SLEEP_BETWEEN_UPLOADS = 0.3
CHECKPOINT_FILE = "checkpoint-optimizacion.json"
REPORT_FILE = "optimizacion-imagenes-reporte.json"

auth = HTTPBasicAuth(USER, APP_PASSWORD)
session = requests.Session()
session.auth = auth
session.headers.update({"User-Agent": "WordPress-Optimizer/1.0"})

stats = {"scanned_media": 0, "images_to_process": 0, "images_processed": 0,
         "images_skipped_not_image": 0, "upload_failures": 0,
         "posts_updated_content": 0, "posts_updated_featured": 0,
         "total_savings_bytes": 0, "start_time": None, "end_time": None}
stop_flag = False

try:
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
except (AttributeError, ValueError):
    pass
def log(msg): print(f"[{datetime.now().strftime('%H:%M:%S')}] {msg}")

def signal_handler(sig, frame):
    global stop_flag
    log("Señal recibida, terminando gracefulmente...")
    stop_flag = True

signal.signal(signal.SIGINT, signal_handler)
signal.signal(signal.SIGTERM, signal_handler)

def get_paginated(endpoint, params=None):
    items = []; page = 1
    wp = f"{WP_URL}/wp-json/wp/v2/{endpoint}"
    p = dict(params or {}); p["per_page"] = BATCH_SIZE
    while True:
        p["page"] = page
        try:
            resp = session.get(wp, params=p, timeout=30)
            if resp.status_code != 200: break
            data = resp.json()
            if not data: break
            items.extend(data)
            total_pages = int(resp.headers.get("X-WP-TotalPages", 0))
            if page % 5 == 0: log(f"  Pagina {page}/{total_pages} ({len(items)} items)")
            if page >= total_pages: break
            page += 1
        except Exception as e:
            log(f"  Error en pagina {page}: {e}"); time.sleep(2); page += 1
    return items

def get_file_extension(url):
    path = unquote(urlparse(url).path)
    m = re.search(r'\.([a-zA-Z0-9]+)(?:\?|$)', path)
    return m.group(1).lower() if m else ""

def is_image_file(name):
    ext = name.lower().rsplit('.', 1)[-1] if '.' in name else ''
    return ext in ('jpg', 'jpeg', 'png', 'gif', 'webp')

def download_image(url):
    resp = session.get(url, timeout=60); resp.raise_for_status()
    return resp.content

def process_to_webp(image_bytes, source_url):
    ext = get_file_extension(source_url)
    if ext == 'webp': return None, None, 0, 0
    img = Image.open(io.BytesIO(image_bytes))
    if img.mode in ("RGBA", "P"):
        img = img.convert("RGBA")
        bg = Image.new("RGBA", img.size, (255, 255, 255, 255))
        img = Image.alpha_composite(bg, img).convert("RGB")
    elif img.mode != "RGB": img = img.convert("RGB")
    orig_w, orig_h = img.size; old_size = len(image_bytes)
    new_w, new_h = orig_w, orig_h
    if orig_w > MAX_WIDTH or orig_h > MAX_HEIGHT:
        ratio = min(MAX_WIDTH / orig_w, MAX_HEIGHT / orig_h)
        new_w, new_h = int(orig_w * ratio), int(orig_h * ratio)
        img = img.resize((new_w, new_h), Image.LANCZOS)
    buf = io.BytesIO()
    img.save(buf, format="WEBP", quality=WEBP_QUALITY, method=6)
    webp_bytes = buf.getvalue()
    base = os.path.splitext(os.path.basename(unquote(urlparse(source_url).path)))[0]
    return webp_bytes, f"{base}.webp", old_size, len(webp_bytes)

def upload_webp(webp_bytes, filename, media_item):
    wp_url = f"{WP_URL}/wp-json/wp/v2/media"
    title = media_item.get("title", {}).get("rendered", filename)
    files = {"file": (filename, webp_bytes, "image/webp")}
    data = {"title": title, "alt_text": media_item.get("alt_text", ""),
            "caption": media_item.get("caption", {}).get("rendered", ""),
            "description": media_item.get("description", {}).get("rendered", "")}
    for attempt in range(3):
        try:
            resp = session.post(wp_url, files=files, data=data, timeout=120)
            if resp.status_code in (200, 201): return resp.json()
            log(f"  Upload fail (attempt {attempt+1}): HTTP {resp.status_code}"); time.sleep(2)
        except Exception as e:
            log(f"  Upload error (attempt {attempt+1}): {e}"); time.sleep(3)
    return None

def analyze_media():
    log("=== FASE 1: ANALISIS DE MEDIA ===")
    all_media = get_paginated("media", {"_fields": "id,title,media_details,source_url,alt_text,caption,description"})
    log(f"Total media items: {len(all_media)}"); stats["scanned_media"] = len(all_media)
    to_process = []; skipped = 0
    for item in all_media:
        src = item.get("source_url", ""); ext = get_file_extension(src)
        if not is_image_file(src) or ext == 'webp': skipped += 1; continue
        to_process.append(item)
    stats["images_to_process"] = len(to_process)
    stats["images_skipped_not_image"] = skipped
    log(f"A procesar: {len(to_process)} | Saltadas (no-img/webp): {skipped}")
    return to_process

def save_checkpoint(mapping, processed_ids):
    with open(CHECKPOINT_FILE, "w") as f:
        json.dump({"mapping": {str(k): v for k, v in mapping.items()},
                    "processed_ids": list(processed_ids),
                    "stats": stats}, f, indent=2)

def load_checkpoint():
    if os.path.exists(CHECKPOINT_FILE):
        with open(CHECKPOINT_FILE) as f:
            cp = json.load(f)
        if "stats" in cp: stats.update(cp["stats"])
        return ({int(k): v for k, v in cp.get("mapping", {}).items()},
                set(cp.get("processed_ids", [])))
    return {}, set()

def process_images(to_process):
    log("\n=== FASE 2: PROCESAR IMAGENES ===")
    mapping, processed_ids = load_checkpoint()
    if mapping: log(f"Checkpoint: {len(mapping)} ya procesadas, reanudando...")
    for idx, item in enumerate(to_process):
        if stop_flag: log("Detenido por señal"); break
        media_id = item["id"]
        if media_id in processed_ids: continue
        src = item.get("source_url", "")
        title_short = item.get("title", {}).get("rendered", "")[:40].encode('ascii', errors='replace').decode('ascii')
        log(f"[{idx+1}/{len(to_process)}] ID {media_id}: {title_short}")
        try:
            img_bytes = download_image(src)
            result = process_to_webp(img_bytes, src)
            if result[0] is None: log(f"  Ya es WebP, saltando"); continue
            webp_bytes, webp_filename, old_size, new_size = result
            new_media = upload_webp(webp_bytes, webp_filename, item)
            if new_media is None: log(f"  ERROR: No se pudo subir"); stats["upload_failures"] += 1; continue
            old_url_base = src.rsplit('.', 1)[0]
            new_url = new_media.get("source_url", ""); new_id = new_media.get("id", 0)
            mapping[media_id] = {"old_id": media_id, "new_id": new_id, "old_url": src,
                                 "new_url": new_url, "old_url_base": old_url_base,
                                 "old_size": old_size, "new_size": new_size,
                                 "file_path": item.get("media_details", {}).get("file", ""),
                                 "title": item.get("title", {}).get("rendered", "")}
            savings = old_size - new_size; stats["total_savings_bytes"] += max(0, savings)
            stats["images_processed"] += 1
            reduction_pct = (1 - new_size / old_size) * 100 if old_size > 0 else 0
            log(f"  -> ID {new_id} | {old_size//1024}KB -> {new_size//1024}KB ({reduction_pct:.0f}% menos)")
            processed_ids.add(media_id)
            if len(mapping) % 10 == 0: save_checkpoint(mapping, processed_ids)
            time.sleep(SLEEP_BETWEEN_UPLOADS)
        except Exception as e:
            log(f"  ERROR ID {media_id}: {e}"); stats["upload_failures"] += 1
    log(f"\nProcesadas: {stats['images_processed']} | Fallos: {stats['upload_failures']}")
    log(f"Ahorro total: {stats['total_savings_bytes'] / 1024:.0f} KB")
    if mapping: save_checkpoint(mapping, processed_ids)
    return mapping

def update_post_content_and_featured(mapping):
    log("\n=== FASE 3: ACTUALIZAR POSTS ===")
    if not mapping: log("No hay mapping para actualizar"); return
    all_posts = get_paginated("posts", {"_fields": "id,title,content,featured_media", "context": "edit"})
    log(f"Total posts: {len(all_posts)}")
    content_updates = 0; featured_updates = 0
    for idx, post in enumerate(all_posts):
        if stop_flag: log("Detenido por señal"); break
        post_id = post["id"]
        content = post.get("content", {}).get("raw", "") or post.get("content", {}).get("rendered", "")
        featured_media_id = post.get("featured_media", 0)
        needs_content_update = False; new_content = content
        # featured image
        if featured_media_id and featured_media_id in mapping:
            nid = mapping[featured_media_id]["new_id"]
            if nid:
                try:
                    r = session.post(f"{WP_URL}/wp-json/wp/v2/posts/{post_id}",
                                     json={"featured_media": nid}, timeout=30)
                    if r.status_code == 200:
                        featured_updates += 1; log(f"  Post {post_id}: featured {featured_media_id}->{nid}")
                except Exception as e: log(f"  Post {post_id}: featured error: {e}")
        # inline content URLs
        old_new_urls = {}
        for info in mapping.values():
            ob = info.get("old_url_base"); nu = info.get("new_url")
            if not ob or not nu: continue
            pat = re.escape(ob) + r'(?:-\d+x\d+)?\.(?:jpg|jpeg|png|gif)'
            for m in re.findall(pat, content): old_new_urls[m] = nu
        if old_new_urls:
            nc = content
            for ou, nu in old_new_urls.items(): nc = nc.replace(ou, nu)
            if nc != content:
                try:
                    r = session.post(f"{WP_URL}/wp-json/wp/v2/posts/{post_id}",
                                     json={"content": nc}, timeout=30)
                    if r.status_code == 200:
                        content_updates += 1
                        log(f"  Post {post_id}: {len(old_new_urls)} URLs reemplazadas")
                except Exception as e: log(f"  Post {post_id}: content error: {e}")
        if (idx+1) % 50 == 0: log(f"  Progreso posts: {idx+1}/{len(all_posts)}")
    stats["posts_updated_content"] = content_updates
    stats["posts_updated_featured"] = featured_updates
    log(f"\nContenido actualizado: {content_updates} | Featured: {featured_updates}")

def generate_report(mapping):
    log("\n=== FASE 4: REPORTE ===")
    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        json.dump({"timestamp": datetime.now().isoformat(), "site": WP_URL,
                    "config": {"max_width": MAX_WIDTH, "webp_quality": WEBP_QUALITY},
                    "stats": stats, "mappings": mapping}, f, indent=2, ensure_ascii=False)
    log(f"Reporte guardado en {REPORT_FILE}")
    print("\n" + "="*60 + "\nRESUMEN DE OPTIMIZACION\n" + "="*60)
    print(f"Media escaneados:       {stats['scanned_media']}")
    print(f"A procesar:             {stats['images_to_process']}")
    print(f"Procesados exitosamente: {stats['images_processed']}")
    print(f"Fallos de subida:       {stats['upload_failures']}")
    print(f"Saltados (no-img/webp): {stats['images_skipped_not_image']}")
    if stats['images_processed'] > 0:
        avg = stats['total_savings_bytes'] / stats['images_processed'] / 1024
        print(f"Ahorro promedio:        {avg:.0f} KB/imagen")
        print(f"Ahorro total:           {stats['total_savings_bytes'] / 1024:.0f} KB")
    print(f"Posts content update:   {stats['posts_updated_content']}")
    print(f"Posts featured update:  {stats['posts_updated_featured']}")
    print("="*60)

def main():
    mode = sys.argv[1] if len(sys.argv) > 1 else "all"
    if mode == "analyze":
        save_checkpoint({}, set())
        to_process = analyze_media()
        generate_report({})
    elif mode == "process":
        mapping, _ = load_checkpoint()
        if not mapping:
            to_process = analyze_media()
            mapping = process_images(to_process)
        else:
            log("Usando checkpoint existente, escaneando media...")
            to_process = analyze_media()
            mapping = process_images(to_process)
        generate_report(mapping)
    elif mode == "update-posts":
        mapping, _ = load_checkpoint()
        if not mapping:
            log("No hay checkpoint, ejecuta primero analyze/process")
            return
        stats["start_time"] = datetime.now().isoformat()
        update_post_content_and_featured(mapping)
        stats["end_time"] = datetime.now().isoformat()
        generate_report(mapping)
    else:
        stats["start_time"] = datetime.now().isoformat()
        to_process = analyze_media()
        if to_process:
            mapping = process_images(to_process)
            if mapping: update_post_content_and_featured(mapping)
        stats["end_time"] = datetime.now().isoformat()
        generate_report(mapping if 'mapping' in dir() else {})
    log("\n=== COMPLETADO ===")

if __name__ == "__main__":
    main()
