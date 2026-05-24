import csv
import re
import requests
from base64 import b64encode

WP_API = "https://infomoteles.cl/wp-json/wp/v2"
auth = b64encode(b"cvieille:QCZg BNQs mcnf HMXE OvBm MXpz").decode()
headers = {"Authorization": f"Basic {auth}"}

fichas = []
with open("entregables/todas-las-fichas.csv", encoding="utf-8-sig") as f:
    reader = csv.DictReader(f)
    for row in reader:
        fichas.append(row)

print(f"Fichas a procesar: {len(fichas)}")

results = []
for i, f in enumerate(fichas):
    if (i+1) % 20 == 0:
        print(f"Procesados: {i+1}/{len(fichas)}")

    slug = f.get("slug", "").strip()
    if not slug:
        continue

    try:
        r = requests.get(f"{WP_API}/posts?slug={slug}", headers=headers, timeout=10)
        posts = r.json()
        if not posts:
            last_slug = slug.rstrip("/").split("/")[-1]
            r = requests.get(f"{WP_API}/posts?slug={last_slug}", headers=headers, timeout=10)
            posts = r.json()

        email = ""
        phone = ""
        title = ""
        if posts:
            content = posts[0].get("content", {}).get("rendered", "")
            title = posts[0].get("title", {}).get("rendered", "")
            emails = re.findall(r"[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}", content)
            if emails:
                email = emails[0]
            phones = re.findall(r"(\+56\s*[\d\s\-\(\)]{7,20}|\b\d{7,12}\b)", content)
            if phones:
                phone = phones[0].strip()

        results.append({
            "slug": slug,
            "title": title,
            "email": email,
            "telefono": phone,
            "clics": f.get("clics", ""),
            "impresiones": f.get("impresiones", ""),
            "ctr": f.get("ctr", ""),
            "posicion": f.get("posicion", ""),
        })
    except Exception as e:
        results.append({
            "slug": slug,
            "title": "",
            "email": f"ERROR: {str(e)[:80]}",
            "telefono": "",
            "clics": f.get("clics", ""),
            "impresiones": f.get("impresiones", ""),
            "ctr": f.get("ctr", ""),
            "posicion": f.get("posicion", ""),
        })

results.sort(key=lambda r: int(r.get("impresiones", 0) or 0), reverse=True)

with open("entregables/fichas-con-contactos.csv", "w", encoding="utf-8-sig", newline="") as f:
    writer = csv.DictWriter(f, fieldnames=["slug", "title", "email", "telefono", "clics", "impresiones", "ctr", "posicion"])
    writer.writeheader()
    writer.writerows(results)

con_email = [r for r in results if r["email"] and not r["email"].startswith("ERROR")]
con_tel = [r for r in results if r["telefono"]]
print(f"\nCon email encontrado: {len(con_email)}")
print(f"Con telefono encontrado: {len(con_tel)}")
print(f"\nTOP 30 CON EMAIL (por impresiones):")
for r in con_email[:30]:
    print(f"  {r['impresiones']:>5} | {r['email']:<35} | {r['slug']}")
