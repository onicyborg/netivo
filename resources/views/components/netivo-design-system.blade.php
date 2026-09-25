<style>
    /* Keep Otika's native geometry intact; only brand and accessibility details live here. */
    html body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif !important; }
    .header-logo { object-fit: contain; }
    .sidebar-brand .header-logo { width: 42px; height: 42px; }

    .antre-profile { display: inline-flex !important; align-items: center; min-height: 48px; padding: 5px 12px 5px 8px !important; border: 1px solid transparent; border-radius: 8px; color: #34395e !important; }
    .antre-profile:hover, .antre-profile[aria-expanded="true"] { border-color: #e4e7f5; background: #f7f8ff; }
    .antre-profile img { flex: 0 0 auto; width: 36px; height: 36px; object-fit: contain; background: #eef0ff; }
    .antre-profile__copy { display: inline-flex; flex-direction: column; min-width: 92px; text-align: left; line-height: 1.25; }
    .antre-profile__name { overflow: hidden; color: #34395e; font-size: 13px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .antre-profile__copy small { color: #98a2b3; font-size: 10px; font-weight: 600; }

    .card-statistic-1 .card-icon, .card-statistic-2 .card-icon, .card-statistic-3 .card-icon, .card-statistic-4 .card-icon { display: flex; align-items: center; justify-content: center; }
    .card-statistic-1 .card-icon i, .card-statistic-1 .card-icon svg, .card-statistic-2 .card-icon i, .card-statistic-2 .card-icon svg, .card-statistic-3 .card-icon i, .card-statistic-3 .card-icon svg, .card-statistic-4 .card-icon i, .card-statistic-4 .card-icon svg { width: 30px; height: 30px; color: #fff; stroke: #fff; font-size: 22px; line-height: 1; }
    .card-statistic-1 .card-icon.bg-warning i, .card-statistic-1 .card-icon.bg-warning svg, .card-statistic-1 .card-icon.bg-light i, .card-statistic-1 .card-icon.bg-light svg { color: #34395e; stroke: #34395e; }
    .sidebar-menu > li > a svg { width: 16px; height: 16px; stroke-width: 2.1; }
    .sidebar-menu > li.active > a svg { color: #6777ef; stroke: #6777ef; }
    .settingSidebar { z-index: 1100; }
    .settingSidebar .settingPanelToggle { z-index: 1101; }

    /* Otika's theme picker is intentionally scoped to the settings drawer. */
    .settingSidebar .choose-theme {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .settingSidebar .choose-theme li {
        width: 24px;
        height: 24px;
        margin: 0 !important;
        padding: 0 !important;
    }

    .settingSidebar .theme-swatch {
        position: relative;
        display: block;
        width: 24px;
        height: 24px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        cursor: pointer;
    }

    .settingSidebar .theme-swatch.white { background: #fff; border: 1px solid #d9dfeb; }
    .settingSidebar .theme-swatch.cyan { background: #3dc9b3; }
    .settingSidebar .theme-swatch.black { background: #343c48; }
    .settingSidebar .theme-swatch.purple { background: #6777ef; }
    .settingSidebar .theme-swatch.orange { background: #ffa117; }
    .settingSidebar .theme-swatch.green { background: #28c76f; }
    .settingSidebar .theme-swatch.red { background: #ea5455; }
    .settingSidebar .choose-theme li.active .theme-swatch::after { content: '✓'; position: absolute; inset: 0; color: #fff; font-size: 14px; font-weight: 800; line-height: 24px; text-align: center; }
    .settingSidebar .choose-theme li.active .theme-swatch.white::after { color: #34395e; }

    /* Align application content with the left edge of the workspace. */
    .main-content > .section { padding-left: 0; padding-right: 0; }
    .main-content > .section > .section-header,
    .main-content > .section > .section-body { padding-left: 0; padding-right: 30px; }
    .main-content > .section > .section-body > .row { margin-left: 0; margin-right: 0; }
    :focus-visible { outline: 3px solid rgba(103, 119, 239, .45); outline-offset: 2px; }

    @media (max-width: 767.98px) { .main-content > .section > .section-header, .main-content > .section > .section-body { padding-left: 15px; padding-right: 15px; } }
    @media (max-width: 575.98px) { .antre-profile { padding-right: 6px !important; } .antre-profile__copy { display: none; } }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; } }
</style>
