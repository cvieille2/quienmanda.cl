import requests


AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
BASE = "https://infomoteles.cl/wp-json/code-snippets/v1/snippets"


CODE = r'''
add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;

    $main_css = get_template_directory() . '/assets/css/main.css';
    if (!file_exists($main_css) || !is_readable($main_css)) {
        return;
    }

    $css = file_get_contents($main_css);
    if (!$css) {
        return;
    }

    echo "\n<style id='orbital-main-css-inline-fallback'>\n" . $css . "\n</style>\n";
}, 1);
'''


def main():
    payload = {
        "name": "orbital-main-css-inline-fallback",
        "code": CODE,
        "scope": "global",
        "active": True,
    }
    resp = requests.post(BASE, json=payload, auth=AUTH, verify=False, timeout=60)
    print(resp.status_code)
    print(resp.text[:800])
    resp.raise_for_status()


if __name__ == "__main__":
    main()
