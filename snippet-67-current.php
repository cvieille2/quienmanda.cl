add_action("wp_enqueue_scripts", function () {
    $css = <<<CSS
.site-header {
  background:#fff;
  border-bottom:1px solid #e5e7eb;
  position:relative;
  z-index:1000;
}
.site-header .header-inner,
.site-header .container.header-inner {
  max-width:100%;
  margin:0 auto;
  padding:18px 20px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:24px;
}
.site-logo a {
  color:#111827;
  font-size:24px;
  font-weight:700;
  text-decoration:none;
}
.header-navigation-wrapper {
  flex:1;
}
.primary-menu-wrapper {
  display:block;
}
.primary-menu {
  list-style:none;
  margin:0;
  padding:0;
  display:flex;
  align-items:center;
  gap:28px;
  flex-wrap:wrap;
}
.primary-menu > li {
  position:relative;
  margin:0;
  padding:0;
}
.primary-menu a {
  color:#111827;
  text-decoration:none;
  font-weight:600;
  display:block;
  padding:12px 0;
}
.primary-menu a:hover {
  color:#e94560;
}
.primary-menu .sub-menu {
  list-style:none;
  margin:0;
  padding:10px 0;
  position:absolute;
  top:100%;
  left:0;
  min-width:260px;
  background:#fff;
  border:1px solid #e5e7eb;
  box-shadow:0 12px 32px rgba(15,23,42,.14);
  display:none;
  z-index:1001;
}
.primary-menu li:hover > .sub-menu,
.primary-menu li.focus > .sub-menu,
.primary-menu li.menu-item-has-children:focus-within > .sub-menu {
  display:block;
}
.primary-menu .sub-menu li {
  margin:0;
}
.primary-menu .sub-menu a {
  padding:10px 16px;
  white-space:nowrap;
}
.mobile-nav-toggle {
  display:none;
}
.menu-modal {
  display:none;
}
@media (max-width: 900px) {
  .header-navigation-wrapper {
    display:none;
  }
  .mobile-nav-toggle {
    display:inline-flex;
  }
  .menu-modal {
    position:fixed;
    inset:0;
    background:#fff;
    overflow:auto;
  }
  body.showing-menu-modal .menu-modal {
    display:block;
  }
  .menu-modal .primary-menu {
    display:block;
  }
  .menu-modal .primary-menu > li {
    position:static;
    border-bottom:1px solid #e5e7eb;
  }
  .menu-modal .primary-menu a {
    padding:14px 0;
  }
  .menu-modal .sub-menu {
    position:static;
    display:block;
    min-width:0;
    border:0;
    box-shadow:none;
    padding:0 0 8px 16px;
  }
}
CSS;
    wp_add_inline_style("orbital-child-style", $css);
}, 20);