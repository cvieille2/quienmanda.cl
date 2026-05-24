import requests


AUTH = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")
URL = "https://infomoteles.cl/wp-json/code-snippets/v1/snippets/67"


SAFE_CODE = r'''add_action("wp_enqueue_scripts", function () {
    $css = <<<CSS
.site-header {
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  position: relative;
  z-index: 1000;
}

.site-header .header-inner,
.site-header .container.header-inner {
  margin: 0 auto;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.site-header .site-logo a {
  color: #111827;
  font-size: 24px;
  font-weight: 700;
  text-decoration: none;
}

.site-header .header-navigation-wrapper {
  flex: 1;
}

.site-header .primary-menu-wrapper {
  display: block;
}

.site-header .primary-menu {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 28px;
  flex-wrap: wrap;
}

.site-header .primary-menu > li {
  position: relative;
  margin: 0;
  padding: 0;
}

.site-header .primary-menu a {
  color: #111827;
  text-decoration: none;
  font-weight: 600;
  display: block;
  padding: 12px 0;
}

.site-header .primary-menu a:hover {
  color: #e94560;
}

.site-header .primary-menu .sub-menu {
  list-style: none;
  margin: 0;
  padding: 10px 0;
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 260px;
  background: #fff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
  display: none;
  z-index: 1001;
}

.site-header .primary-menu li:hover > .sub-menu,
.site-header .primary-menu li.focus > .sub-menu,
.site-header .primary-menu li.menu-item-has-children:focus-within > .sub-menu {
  display: block;
}

.site-header .primary-menu .sub-menu li {
  margin: 0;
}

.site-header .primary-menu .sub-menu a {
  padding: 10px 16px;
  white-space: nowrap;
}

.site-header .mobile-nav-toggle {
  display: none;
}

.layout-menu-orbital .menu-modal {
  display: none;
}

@media (max-width: 900px) {
  .site-header .header-navigation-wrapper {
    display: none;
  }

  .site-header .mobile-nav-toggle {
    display: inline-flex;
  }

  .layout-menu-orbital .menu-modal {
    position: fixed;
    inset: 0;
    background: #fff;
    overflow: auto;
  }

  body.showing-menu-modal.layout-menu-orbital .menu-modal {
    display: block;
  }

  .layout-menu-orbital .menu-modal .primary-menu {
    display: block;
  }

  .layout-menu-orbital .menu-modal .primary-menu > li {
    position: static;
    border-bottom: 1px solid #e5e7eb;
  }

  .layout-menu-orbital .menu-modal .primary-menu a {
    padding: 14px 0;
  }

  .layout-menu-orbital .menu-modal .sub-menu {
    position: static;
    display: block;
    min-width: 0;
    border: 0;
    box-shadow: none;
    padding: 0 0 8px 16px;
  }
}
CSS;

    wp_add_inline_style("orbital-child-style", $css);
}, 20);
'''


def main():
    payload = {
        "name": "orbital-menu-restore-inline",
        "code": SAFE_CODE,
        "scope": "global",
        "active": True,
    }

    resp = requests.put(URL, json=payload, auth=AUTH, verify=False, timeout=60)
    if resp.status_code >= 400:
        resp = requests.post(URL, json=payload, auth=AUTH, verify=False, timeout=60)

    print(resp.status_code)
    print(resp.text[:700])
    resp.raise_for_status()


if __name__ == "__main__":
    main()
