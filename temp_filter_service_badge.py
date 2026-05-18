import html
import re
import subprocess

COOKIE = r"C:/Users/Usuario/AppData/Local/Temp/opencode/infomoteles.cookies"
EDITOR_URL = "https://infomoteles.cl/wp-admin/theme-editor.php?theme=orbital-child&file=single.php"

page = subprocess.run(["curl", "-s", "-b", COOKIE, EDITOR_URL], capture_output=True, check=True).stdout.decode("utf-8", errors="replace")

nonce_match = re.search(r'<input type="hidden" id="nonce" name="nonce" value="([^"]+)"', page)
textarea_match = re.search(r'<textarea[^>]*name="newcontent"[^>]*>(.*?)</textarea>', page, re.S)

if not nonce_match or not textarea_match:
    raise SystemExit("Could not parse theme editor page")

nonce = nonce_match.group(1)
content = html.unescape(textarea_match.group(1))

old = "          <?php foreach (array_slice((array) $servicios, 0, 2) as $sv): ?><span class=\"motel-badge\"><?php echo esc_html($sv); ?></span><?php endforeach; ?>\n"
new = "          <?php foreach (array_slice((array) $servicios, 0, 2) as $sv): ?><?php if ($ciudad && mb_strtolower(trim($sv)) === mb_strtolower(trim($ciudad))) { continue; } ?><span class=\"motel-badge\"><?php echo esc_html($sv); ?></span><?php endforeach; ?>\n"

if old not in content:
    old = old.rstrip("\n")
    new = new.rstrip("\n")

if old not in content:
    raise SystemExit("Service badge loop not found")

content = content.replace(old, new, 1)

meta_old = "        <div class=\"motel-meta\">\n          <span><?php echo esc_html($direccion ?: ($ciudad ?: 'Chile')); ?></span>\n          <span class=\"motel-dot\"></span>\n          <span><?php echo esc_html($comuna ?: ''); ?></span>\n        </div>\n\n"
content = content.replace(meta_old, "", 1)

content = content.replace('.motel-meta{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:14px;color:#475569;font-size:14px;}', '.motel-meta{display:none;}')
content = content.replace('.motel-badges{display:flex;flex-wrap:wrap;gap:10px;margin:18px 0 0;}', '.motel-badges{display:none;}')

temp_path = r"C:/Users/Usuario/AppData/Local/Temp/opencode/single.php.modified"
with open(temp_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(content)

result = subprocess.run([
    "curl", "-s", "-b", COOKIE,
    "-X", "POST",
    "--data-urlencode", f"nonce={nonce}",
    "--data-urlencode", "action=update",
    "--data-urlencode", "file=single.php",
    "--data-urlencode", "theme=orbital-child",
    "--data-urlencode", "_wp_http_referer=/wp-admin/theme-editor.php?theme=orbital-child&file=single.php",
    "--data-urlencode", f"newcontent@{temp_path}",
    EDITOR_URL,
], capture_output=True, check=True)

print(result.stdout.decode("utf-8", errors="replace"))
