import base64
import json
import subprocess

POST_ID = 2169
BASE = f"https://infomoteles.cl/wp-json/wp/v2/posts/{POST_ID}"
CREDS = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
COOKIE = r"C:/Users/Usuario/AppData/Local/Temp/opencode/infomoteles.cookies"
NONCE = "6708885e45"

def curl_json(args, input_bytes=None):
    proc = subprocess.run(args, input=input_bytes, capture_output=True, check=True)
    return proc.stdout.decode("utf-8", errors="replace")

raw = curl_json([
    "curl",
    "-s",
    "-u",
    CREDS,
    f"{BASE}?context=edit",
])
data = json.loads(raw)
content = data["content"]["raw"]

marker = "document.querySelectorAll(\"a\").forEach"
script = (
    "\n<script>\n"
    "document.addEventListener(\"DOMContentLoaded\", function () {\n"
    "  document.querySelectorAll(\"a\").forEach(function (a) {\n"
    "    if (a.textContent.trim() === \"RESERVAR\") {\n"
    "      a.textContent = \"Ver tarifas\";\n"
    "    }\n"
    "  });\n"
    "});\n"
    "</script>\n"
)

if marker not in content:
    content += script

payload = json.dumps({"content": content}).encode("utf-8")

updated = curl_json([
    "curl",
    "-s",
    "-b",
    COOKIE,
    "-H",
    f"X-WP-Nonce: {NONCE}",
    "-H",
    "Content-Type: application/json",
    "-X",
    "POST",
    "--data-binary",
    "@-",
    BASE,
], input_bytes=payload)

print(updated)
