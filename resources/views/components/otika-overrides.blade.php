<style>
    html body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif !important; }
    .header-logo { object-fit: contain; }
    .main-navbar .nav-link-user,
    .main-navbar .nav-link-user > span { color: #34395e !important; }
    .main-navbar .nav-link-user:hover,
    .main-navbar .nav-link-user:focus,
    .main-navbar .nav-link-user[aria-expanded="true"] { color: #6777ef !important; }
    .main-navbar .navbar-right > li + li { margin-left: 12px; }
    .main-navbar .nav-link-user { display: inline-flex !important; align-items: center; gap: 9px; }
    .main-navbar .nav-link-user .nav-user-copy { display: inline-flex; flex-direction: column; min-width: 0; line-height: 1.2; text-align: left; }
    .main-navbar .nav-link-user .nav-user-name { max-width: 150px; overflow: hidden; color: #34395e; font-size: 13px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .main-navbar .nav-link-user .nav-user-role { color: #98a2b3; font-size: 10px; font-weight: 600; }
    .main-navbar .nav-link-user .nav-user-chevron { width: 14px; height: 14px; color: #8b96a7; stroke-width: 2; }
    .sidebar-brand > a { display: inline-flex !important; align-items: center; gap: 10px; }
    .settingSidebar { z-index: 1100; }
    .settingSidebar .choose-theme { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin: 0; }
    .settingSidebar .theme-swatch { width: 24px; height: 24px; padding: 0; border: 0; border-radius: 50%; cursor: pointer; }
    .settingSidebar .theme-swatch.white { background: #fff; border: 1px solid #d9dfeb; }
    .settingSidebar .theme-swatch.cyan { background: #3dc9b3; }
    .settingSidebar .theme-swatch.black { background: #343c48; }
    .settingSidebar .theme-swatch.purple { background: #6777ef; }
    .settingSidebar .theme-swatch.orange { background: #ffa117; }
    .settingSidebar .theme-swatch.green { background: #28c76f; }
    .settingSidebar .theme-swatch.red { background: #ea5455; }
    .main-sidebar .sidebar-brand .header-logo { max-width: 42px; max-height: 42px; }
    :focus-visible { outline: 3px solid rgba(103,119,239,.45); outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; } }
    @media (max-width: 575.98px) { .main-navbar .nav-link-user .nav-user-role { display: none; } .main-navbar .nav-link-user .nav-user-name { max-width: 92px; } .main-navbar .navbar-right > li + li { margin-left: 6px; } }
</style>
