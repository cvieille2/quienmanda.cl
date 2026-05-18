import json
import requests


BASE = "https://infomoteles.cl"
AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")


SNIPPET_CODE = r'''<?php
add_action('rest_api_init', function () {
    register_rest_route('im/v1', '/theme-config', array(
        'methods'  => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $theme = wp_get_theme();
            $parent = $theme->parent();

            $mods = get_theme_mods();
            if (!is_array($mods)) {
                $mods = array();
            }

            $width_mods = array();
            foreach ($mods as $k => $v) {
                if (preg_match('/(width|container|layout|boxed|content)/i', (string) $k)) {
                    $width_mods[$k] = $v;
                }
            }

            $paths = array(
                get_theme_root() . '/orbital/style.css',
                get_theme_root() . '/orbital/functions.php',
                get_theme_root() . '/orbital-child/style.css',
                get_theme_root() . '/orbital-child/functions.php',
                get_theme_root() . '/orbital-child/single-ficha_motel.php',
            );

            $files = array();
            foreach ($paths as $path) {
                if (file_exists($path) && is_readable($path)) {
                    $content = file_get_contents($path);
                    $lines = preg_split('/\R/', (string) $content);
                    $hits = array();
                    foreach ($lines as $i => $line) {
                        if (preg_match('/(container|content-width|max-width|site-width|layout|boxed|theme_mod|customize_register)/i', $line)) {
                            $hits[] = array('line' => $i + 1, 'text' => trim($line));
                        }
                    }
                    $files[] = array(
                        'path' => $path,
                        'hits' => array_slice($hits, 0, 120),
                    );
                } else {
                    $files[] = array(
                        'path' => $path,
                        'missing' => true,
                    );
                }
            }

            $scan_dirs = array(
                get_theme_root() . '/orbital',
                get_theme_root() . '/orbital-child',
            );
            $scan_hits = array();
            foreach ($scan_dirs as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                foreach ($it as $file) {
                    if ($file->isDir()) {
                        continue;
                    }
                    $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                    if (!in_array($ext, array('php', 'css'))) {
                        continue;
                    }
                    $path = $file->getPathname();
                    $raw = @file_get_contents($path);
                    if ($raw === false) {
                        continue;
                    }
                    if (!preg_match('/(container|content-width|max-width|site-width|layout|boxed|customize_register)/i', $raw)) {
                        continue;
                    }
                    $local_hits = array();
                    $lines = preg_split('/\R/', $raw);
                    foreach ($lines as $i => $line) {
                        if (preg_match('/(container|content-width|max-width|site-width|layout|boxed|customize_register)/i', $line)) {
                            $local_hits[] = array('line' => $i + 1, 'text' => trim($line));
                        }
                    }
                    $scan_hits[] = array(
                        'path' => $path,
                        'hits' => array_slice($local_hits, 0, 40),
                    );
                }
            }

            return array(
                'active' => array(
                    'stylesheet' => $theme->get_stylesheet(),
                    'template' => $theme->get_template(),
                    'name' => $theme->get('Name'),
                    'version' => $theme->get('Version'),
                ),
                'parent' => $parent ? array(
                    'stylesheet' => $parent->get_stylesheet(),
                    'template' => $parent->get_template(),
                    'name' => $parent->get('Name'),
                    'version' => $parent->get('Version'),
                ) : null,
                'width_mods' => $width_mods,
                'files' => $files,
                'scan_hits' => array_slice($scan_hits, 0, 200),
            );
        },
    ));
});
return 'inspect-orbital-config active';
'''


def main():
    create = requests.post(
        f"{BASE}/wp-json/code-snippets/v1/snippets",
        json={
            "name": "inspect-orbital-config-temp",
            "code": SNIPPET_CODE,
            "scope": "global",
            "active": True,
        },
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    print("create", create.status_code)
    create.raise_for_status()
    snippet = create.json()
    sid = snippet["id"]

    resp = requests.get(
        f"{BASE}/wp-json/im/v1/theme-config",
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    print("inspect", resp.status_code)
    resp.raise_for_status()
    data = resp.json()

    out_path = "orbital-theme-config-live.json"
    with open(out_path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print("saved", out_path)

    requests.post(
        f"{BASE}/wp-json/code-snippets/v1/snippets/{sid}/deactivate",
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    requests.delete(
        f"{BASE}/wp-json/code-snippets/v1/snippets/{sid}",
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    print("cleanup done", sid)


if __name__ == "__main__":
    main()
