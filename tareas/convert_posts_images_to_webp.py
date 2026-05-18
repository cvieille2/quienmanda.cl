import argparse
import io
import json
import mimetypes
import re
from pathlib import Path
from urllib.parse import urlparse

import requests
from PIL import Image
from requests.auth import HTTPBasicAuth


BASE_URL = "https://infomoteles.cl/wp-json/wp/v2"
AUTH_USER = "cvieille"
AUTH_PASS = "QCZg BNQs mcnf HMXE OvBm MXpz"
DEFAULT_EXPORT = Path(__file__).with_name("infomoteles-posts.json")


IMG_RE = re.compile(r'<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>', re.IGNORECASE)


def wp_session():
    s = requests.Session()
    s.auth = HTTPBasicAuth(AUTH_USER, AUTH_PASS)
    return s


def get_post(session, post_id):
    r = session.get(f"{BASE_URL}/posts/{post_id}", params={"_fields": "id,title,content,slug,link,modified"}, timeout=60)
    r.raise_for_status()
    return r.json()


def update_post(session, post_id, content):
    r = session.post(f"{BASE_URL}/posts/{post_id}", json={"content": content}, timeout=60)
    r.raise_for_status()
    return r.json()


def upload_webp(session, source_url, image_bytes, filename_hint):
    headers = {"Content-Disposition": f'attachment; filename="{filename_hint}"'}
    mime = mimetypes.guess_type(filename_hint)[0] or "image/webp"
    files = {"file": (filename_hint, image_bytes, mime)}
    r = session.post(f"{BASE_URL}/media", headers=headers, files=files, timeout=120)
    r.raise_for_status()
    return r.json()
def bytes_to_webp(image_bytes):
    with Image.open(io.BytesIO(image_bytes)) as img:
        if img.mode not in ("RGB", "RGBA"):
            img = img.convert("RGBA" if "A" in img.getbands() else "RGB")
        out = io.BytesIO()
        img.save(out, format="WEBP", quality=82, method=6)
        return out.getvalue()


def make_webp_name(url):
    name = Path(urlparse(url).path).name
    stem = Path(name).stem
    return f"{stem}.webp"


def process_content(session, content):
    replacements = {}
    for match in IMG_RE.finditer(content):
        src = match.group(1)
        if src.lower().endswith(".webp"):
            continue
        if src in replacements:
            continue
        try:
            r = session.get(src, timeout=120)
            r.raise_for_status()
            webp_bytes = bytes_to_webp(r.content)
            uploaded = upload_webp(session, src, webp_bytes, make_webp_name(src))
            new_url = uploaded.get("source_url")
            if new_url:
                replacements[src] = new_url
        except Exception as exc:
            print(f"  skip imagen rota {src} ({exc})")

    updated = content
    for old, new in replacements.items():
        updated = updated.replace(old, new)
    return updated, replacements


def load_posts(export_path):
    with open(export_path, encoding="utf-8") as f:
        return json.load(f)


def main():
    parser = argparse.ArgumentParser(description="Convert post images to WebP and update WordPress content.")
    parser.add_argument("--export", default=str(DEFAULT_EXPORT), help="Path to post export JSON")
    parser.add_argument("--limit", type=int, default=0, help="Process only the first N posts")
    parser.add_argument("--dry-run", action="store_true", help="Do not write updates")
    args = parser.parse_args()

    posts = load_posts(args.export)
    if args.limit:
        posts = posts[:args.limit]

    session = wp_session()
    changed_posts = 0
    converted_images = 0

    for idx, post in enumerate(posts, start=1):
        post_id = post.get("id")
        if not post_id:
            continue
        full = get_post(session, post_id)
        content = full.get("content", {}).get("rendered", "")
        if not content:
            continue

        new_content, replacements = process_content(session, content)
        if not replacements:
            print(f"[{idx}] {post_id} sin imágenes para convertir")
            continue

        converted_images += len(replacements)
        if new_content != content:
            changed_posts += 1
            if args.dry_run:
                print(f"[{idx}] DRY {post_id} {len(replacements)} imágenes")
            else:
                update_post(session, post_id, new_content)
                print(f"[{idx}] OK {post_id} {len(replacements)} imágenes")

    print(f"Done posts={changed_posts} images={converted_images}")


if __name__ == "__main__":
    main()
