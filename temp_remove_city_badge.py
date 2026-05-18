import html
import os
import re
import subprocess

COOKIE = r"C:/Users/Usuario/AppData/Local/Temp/opencode/infomoteles.cookies"
EDITOR_URL = "https://infomoteles.cl/wp-admin/theme-editor.php?theme=orbital-child&file=single.php"

page = subprocess.run([
    "curl", "-s", "-b", COOKIE, EDITOR_URL
], capture_output=True, check=True).stdout.decode("utf-8", errors="replace")

nonce_match = re.search(r'<input type="hidden" id="nonce" name="nonce" value="([^"]+)"', page)
textarea_match = re.search(r'<textarea[^>]*name="newcontent"[^>]*>(.*?)</textarea>', page, re.S)

if not nonce_match or not textarea_match:
    raise SystemExit("Could not parse theme editor page")

nonce = nonce_match.group(1)
content = html.unescape(textarea_match.group(1))

old = "          <?php if ($ciudad): ?><span class=\"motel-badge\"><?php echo esc_html($ciudad); ?></span><?php endif; ?>\n"
if old not in content:
    old = "          <?php if ($ciudad): ?><span class=\"motel-badge\"><?php echo esc_html($ciudad); ?></span><?php endif; ?>"

if old not in content:
    raise SystemExit("City badge line not found")

content = content.replace(old, "")

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
