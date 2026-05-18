import json
import requests


BASE = "https://infomoteles.cl"
AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")


SNIPPET_CODE = r'''<?php
add_action('rest_api_init', function () {
    register_rest_route('im/v1', '/theme-files-head', array(
        'methods'  => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $paths = array(
                get_theme_root() . '/orbital/style.css',
                get_theme_root() . '/orbital/functions.php',
                get_theme_root() . '/orbital/inc/customizer-parts/customizer-control.php',
                get_theme_root() . '/orbital/inc/customizer-parts/customizer-options.php',
                get_theme_root() . '/orbital/assets/css/main.css',
                get_theme_root() . '/orbital-child/style.css',
                get_theme_root() . '/orbital-child/functions.php',
                get_theme_root() . '/orbital-child/single-ficha_motel.php',
            );

            $out = array();
            foreach ($paths as $path) {
                if (!file_exists($path) || !is_readable($path)) {
                    $out[] = array('path' => $path, 'missing' => true);
                    continue;
                }
                $raw = file_get_contents($path);
                $lines = preg_split('/\R/', (string) $raw);
                $out[] = array(
                    'path' => $path,
                    'head' => array_slice($lines, 0, 220),
                );
            }

            return $out;
        },
    ));
});
return 'theme file head inspector active';
'''


def main():
    create = requests.post(
        f"{BASE}/wp-json/code-snippets/v1/snippets",
        json={"name": "inspect-theme-file-heads-temp", "code": SNIPPET_CODE, "scope": "global", "active": True},
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    create.raise_for_status()
    sid = create.json()["id"]

    resp = requests.get(
        f"{BASE}/wp-json/im/v1/theme-files-head",
        auth=AUTH,
        verify=False,
        timeout=60,
    )
    resp.raise_for_status()

    with open("orbital-theme-files-heads.json", "w", encoding="utf-8") as f:
        json.dump(resp.json(), f, ensure_ascii=False, indent=2)
    print("saved orbital-theme-files-heads.json")

    requests.post(f"{BASE}/wp-json/code-snippets/v1/snippets/{sid}/deactivate", auth=AUTH, verify=False, timeout=60)
    requests.delete(f"{BASE}/wp-json/code-snippets/v1/snippets/{sid}", auth=AUTH, verify=False, timeout=60)
    print("cleanup", sid)


if __name__ == "__main__":
    main()
