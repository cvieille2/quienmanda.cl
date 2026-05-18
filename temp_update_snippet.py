import json
import subprocess

SNIPPET_ID = 120
BASE = f"https://infomoteles.cl/wp-json/code-snippets/v1/snippets/{SNIPPET_ID}"
CREDS = "cvieille:QCZg BNQs mcnf HMXE OvBm MXpz"
COOKIE = r"C:/Users/Usuario/AppData/Local/Temp/opencode/infomoteles.cookies"

CODE = """
add_action('admin_init', function () {
    if (get_option('im_motel_single_patched')) {
        return;
    }

    $file = get_stylesheet_directory() . '/single.php';
    if (!is_readable($file) || !is_writable($file)) {
        return;
    }

    $code = file_get_contents($file);
    if (strpos($code, 'Ver tarifas') !== false && strpos($code, 'Tarifas y habitaciones') !== false) {
        update_option('im_motel_single_patched', 1, false);
        return;
    }

    $new_block = <<<'HTML'
      <section id="tarifas" class="motel-section motel-card motel-hide">
        <h2>Tarifas y habitaciones</h2>
        <div class="motel-tablewrap">
          <?php if (!empty($tarifas)): ?>
            <table class="motel-table">
              <thead><tr><th>Habitacion</th><th>Tiempo</th><th>Precio</th><th>Incluye</th></tr></thead>
              <tbody>
                <?php foreach ((array) $tarifas as $t): ?><tr><td><?php echo esc_html($t['habitacion'] ?? ''); ?></td><td><?php echo esc_html($t['tiempo'] ?? ''); ?></td><td><?php echo !empty($t['precio']) ? '$' . number_format((int) $t['precio'], 0, ',', '.') : ''; ?></td><td><?php echo esc_html($t['incluye'] ?? ''); ?></td></tr><?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="motel-copy">Tarifas por confirmar. Contacta por telefono o WhatsApp para validar disponibilidad y precio actualizado.</p>
          <?php endif; ?>
        </div>
      </section>
HTML;

    $code = preg_replace('/\n      <\?php if \(!empty\(\$tarifas\)\): \?>.*?\n      <\?php endif; \?>/s', "\n" . $new_block, $code, 1);
    $code = str_replace('RESERVAR', 'Ver tarifas', $code);

    if ($code !== null && file_put_contents($file, $code) !== false) {
        update_option('im_motel_single_patched', 1, false);
    }
});
"""


def run(args, input_bytes=None):
    proc = subprocess.run(args, input=input_bytes, capture_output=True, check=True)
    return proc.stdout.decode("utf-8", errors="replace")


payload = json.dumps({
    "name": "Patch single motel template",
    "description": "Parchea el single.php del child theme para mostrar tarifas y cambiar el CTA.",
    "code": CODE,
    "tags": ["infomoteles", "single", "cta", "tarifas"],
    "scope": "global",
    "active": True,
    "priority": 10,
    "network": False,
    "shared_network": False,
}).encode("utf-8")

updated = run([
    "curl",
    "-s",
    "-u",
    CREDS,
    "-H",
    "Content-Type: application/json",
    "-X",
    "PUT",
    "--data-binary",
    "@-",
    BASE,
], input_bytes=payload)

print(updated)

# Trigger an admin request so the active snippet executes.
run([
    "curl",
    "-s",
    "-b",
    COOKIE,
    "https://infomoteles.cl/wp-admin/",
])
