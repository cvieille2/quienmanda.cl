import csv
import base64
import html
import json
import pathlib
import re
import unicodedata
import urllib.request
from collections import defaultdict

ROOT = pathlib.Path(r"C:\Users\Usuario\Desktop\multiplicar-dinero")
HTML_FILE = ROOT / "input" / "Keyword Stats 2026-05-22 at 13_13_12.html"
OUT_CSV = ROOT / "agentes" / "agente-director-agencia-digital" / "output" / "plan" / "T-037-cobertura-completa.csv"
OUT_MD = ROOT / "agentes" / "agente-director-agencia-digital" / "output" / "plan" / "T-037-cobertura-completa.md"

CREDS = ("cvieille", "u0wM 1VRi v9wL Z71R 7XCx mnEq")
AUTH = "Basic " + base64.b64encode(f"{CREDS[0]}:{CREDS[1]}".encode()).decode()
BASE = "https://avesnativaschilenas.cl/wp-json/wp/v2"


def api(endpoint: str):
    req = urllib.request.Request(BASE + endpoint, method="GET")
    req.add_header("Authorization", AUTH)
    req.add_header("User-Agent", "opencode/1.0")
    with urllib.request.urlopen(req, timeout=60) as resp:
        return resp, json.loads(resp.read())


def norm(s: str) -> str:
    s = s.lower()
    s = "".join(c for c in unicodedata.normalize("NFD", s) if unicodedata.category(c) != "Mn")
    s = re.sub(r"[^a-z0-9]+", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def load_keywords():
    text = HTML_FILE.read_text(encoding="utf-8")
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", text, re.S)
    kws = []
    for row in rows[1:]:
        cells = re.findall(r"<t[dh][^>]*>(.*?)</t[dh]>", row, re.S)
        vals = [html.unescape(re.sub(r"<[^>]+>", "", c).strip()) for c in cells]
        if len(vals) >= 2 and vals[1] != "Keyword":
            kws.append(vals[1])
    return kws


def load_inventory():
    items = []
    for kind in ["posts", "pages"]:
        resp, _ = api(f"/{kind}?per_page=1")
        total = int(resp.headers.get("X-WP-Total"))
        pages = (total + 99) // 100
        for page in range(1, pages + 1):
            _, batch = api(f"/{kind}?per_page=100&page={page}&_fields=id,slug,title,link")
            for p in batch:
                title = p["title"]["rendered"] if isinstance(p["title"], dict) else p["title"]
                title = html.unescape(re.sub(r"<[^>]+>", "", title))
                items.append({
                    "kind": kind[:-1],
                    "id": p["id"],
                    "slug": p["slug"],
                    "title": title,
                    "link": p["link"],
                    "norm": norm(f"{title} {p['slug']} {p['link']}")
                })
    return items


def intent(k: str) -> str:
    s = norm(k)
    if any(x in s for x in ["mercadolibre", "amazon", "facebook", "instagram", "www", "http"]):
        return "navegacional"
    if any(x in s for x in ["comprar", "compra", "precio", "precios", "venta", "vender", "tienda", "mejor", "mejores", "guia de compra", "comparativa", "segunda mano", "usada", "usado", "barata", "barato"]):
        return "comercial"
    if any(x in s for x in ["como", "que es", "donde", "cuando", "por que", "significado", "significa", "caracteristicas", "tipos", "alimentacion", "cuidados", "habitat", "dieta", "informacion", "especies", "familia", "historia", "curiosidades", "proteger", "conservar", "extincion"]):
        return "informacional"
    if any(x in s for x in ["jaula", "aviario", "voladera", "pajarera", "comedero", "bebedero", "nido", "percha", "accesorio", "juguete", "marca", "modelo", "transportin"]):
        return "mixto"
    return "mixto"


SPECIAL = [
    ("/jaulas/", ["jaula", "jaulas", "jaula para pajaros", "jaula para aves", "jaulas para aves"]),
    ("/tienda/comederos/", ["comedero", "comederos", "bebedero", "bebederos"]),
    ("/jaulas/accesorios-para-jaulas/", ["accesorio", "accesorios", "percha", "perchas", "juguete", "juguetes", "funda", "fundas", "cubierta", "cubiertas"]),
    ("/jaulas/para-loros/", ["loro", "loros", "guacamayo", "cacatua", "agapornis", "cotorra", "yaco", "inseparable", "lovebird", "pyrrhura", "choroy", "tricahue"]),
    ("/jaulas/para-periquitos/", ["periquito", "periquitos", "perico", "pericos", "australiano"]),
    ("/jaulas/para-ninfas/", ["ninfa", "ninfas", "cockatiel", "cocatiel"]),
    ("/jaulas/para-canarios/", ["canario", "canarios", "timbrado", "timbrados"]),
    ("/jaulas/para-gallinas-ponedoras/", ["gallina", "gallinas", "ponedora", "ponedoras", "gallo", "gallos", "codorniz", "codornices", "perdiz", "perdices", "pollos", "pollitos"]),
    ("/jaulas/marcas/", ["pedros", "2gr", "vision", "hagen", "imac", "voltrega", "ferplast", "terenziani", "dival", "copele", "zolia", "pawhut", "montana madeira"]),
    ("/jaulas/aviarios/", ["aviario", "aviarios"]),
    ("/jaulas/voladeras/", ["voladera", "voladeras", "voladero", "voladeros"]),
    ("/jaulas/pajareras/", ["pajarera", "pajareras"]),
    ("/jaulas/nidos-para-aves/", ["nido", "nidos"]),
    ("/jaulas/transportines-para-aves/", ["transportin", "transportines"]),
]


def find_best_match(keyword: str, inventory):
    nk = norm(keyword)
    ktoks = set(nk.split())
    best = None
    bestscore = 0
    for url, tokens in SPECIAL:
        if any(t in nk for t in tokens):
            for it in inventory:
                if url.strip("/") in it["link"] or it["link"].endswith(url):
                    return it, 100, True
    for it in inventory:
        ttoks = set(it["norm"].split())
        score = len(ktoks & ttoks)
        if score > bestscore:
            bestscore = score
            best = it
    return best, bestscore, False


def main():
    keywords = load_keywords()
    inventory = load_inventory()

    rows = []
    counts = defaultdict(int)
    intents = defaultdict(int)

    for kw in keywords:
        ik = intent(kw)
        intents[ik] += 1
        best, score, special = find_best_match(kw, inventory)
        if special or score >= 3:
            coverage = "SI"
        elif score >= 2:
            coverage = "SI/Parcial"
        else:
            coverage = "NO"
            best = None
        counts[coverage] += 1
        rows.append({
            "keyword": kw,
            "intencion": ik,
            "cobertura": coverage,
            "tipo": best["kind"] if best else "",
            "url": best["link"] if best else "",
            "titulo": best["title"] if best else "",
            "id": best["id"] if best else "",
            "score": score,
        })

    OUT_CSV.parent.mkdir(parents=True, exist_ok=True)
    with OUT_CSV.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(f, fieldnames=["keyword", "intencion", "cobertura", "tipo", "url", "titulo", "id", "score"])
        writer.writeheader()
        writer.writerows(rows)

    with OUT_MD.open("w", encoding="utf-8") as f:
        f.write("# T-037 Cobertura Completa\n\n")
        f.write("## Resumen\n")
        f.write(f"- Total keywords: {len(rows)}\n")
        f.write(f"- SI: {counts['SI']}\n")
        f.write(f"- SI/Parcial: {counts['SI/Parcial']}\n")
        f.write(f"- NO: {counts['NO']}\n\n")
        f.write("## Distribucion por intencion\n")
        for k in ["comercial", "informacional", "navegacional", "mixto"]:
            f.write(f"- {k}: {intents[k]}\n")
        f.write("\n## Archivo de detalle\n")
        f.write("- `output/plan/T-037-cobertura-completa.csv`\n")

    print(f"Wrote {OUT_CSV}")
    print(f"Wrote {OUT_MD}")
    print(dict(counts))


if __name__ == "__main__":
    main()
