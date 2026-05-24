import requests


AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
BASE = "https://infomoteles.cl/wp-json/code-snippets/v1/snippets/112"


NEW_CODE = r'''
add_action('wp_head', function () {
    if (is_admin() || !is_singular('post') || has_category('blog')) {
        return;
    }

    echo '<style>
    .motel-grid,
    .single-ficha_motel .ficha-grid {
        width: 100% !important;
        grid-template-columns: 1fr !important;
    }
    .motel-main,
    .single-ficha_motel .ficha-main {
        width: 100% !important;
    }
    .motel-card,
    .motel-section,
    .single-ficha_motel .ficha-card,
    .single-ficha_motel .ficha-section {
        width: 100% !important;
        box-sizing: border-box;
    }
    </style>';
}, 99);
'''


def main():
    payload = {
        "name": "motel-fullwidth-shell-override",
        "code": NEW_CODE,
        "scope": "global",
        "active": True,
    }

    resp = requests.put(BASE, json=payload, auth=AUTH, verify=False, timeout=60)
    if resp.status_code >= 400:
        # fallback some installs expect POST for updates
        resp = requests.post(BASE, json=payload, auth=AUTH, verify=False, timeout=60)

    print(resp.status_code)
    print(resp.text[:600])
    resp.raise_for_status()


if __name__ == "__main__":
    main()
