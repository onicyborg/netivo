<style>
    .dashboard-content > .dashboard-section { margin-bottom: 1.5rem; }
    .dashboard-content > .dashboard-section:last-child { margin-bottom: 0; }
    .dashboard-kpi { overflow: hidden; border: 1px solid #e8edf5; border-radius: 8px; box-shadow: 0 4px 14px rgba(52, 57, 94, .06); }
    .dashboard-kpi .card-body { display: flex !important; flex-direction: row; align-items: center !important; padding: 1rem 1.15rem; }
    .dashboard-kpi__icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 48px; width: 48px; height: 48px; border-radius: 12px; }
    .dashboard-kpi__icon svg { width: 22px; height: 22px; stroke-width: 2.2; }
    .dashboard-kpi__copy { min-width: 0; margin-left: 14px; }
    .dashboard-kpi__label { overflow: hidden; color: #7a869a; font-size: 11px; font-weight: 700; letter-spacing: .04em; line-height: 1.3; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
    .dashboard-kpi__value { margin-top: 4px; color: #34395e; font-size: 21px; font-weight: 800; line-height: 1.15; overflow-wrap: anywhere; }
    .dashboard-kpi__description { min-height: 18px; margin-top: 4px; color: #98a2b3; font-size: 11px; line-height: 1.35; }
    .dashboard-kpi--neutral .dashboard-kpi__icon { background: #edf2ff; color: #5368d8; }
    .dashboard-kpi--positive .dashboard-kpi__icon { background: #e7f7ed; color: #218653; }
    .dashboard-kpi--warning .dashboard-kpi__icon { background: #fff2dc; color: #a86300; }
    .dashboard-kpi--danger .dashboard-kpi__icon { background: #ffebeb; color: #c43f4d; }
    .dashboard-kpi--info .dashboard-kpi__icon { background: #e5f5fb; color: #187493; }
    .dashboard-panel-row > [class*="col-"] { margin-bottom: 1.5rem; }
    .dashboard-panel-row > [class*="col-"]:last-child { margin-bottom: 0; }
    .dashboard-empty-state { padding: 1.75rem 1rem; color: #6c757d; text-align: center; }
    .dashboard-empty-state__icon { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; margin-bottom: .75rem; border-radius: 50%; background: #f0f2f5; color: #7b8794; }
    .dashboard-empty-state__icon svg { width: 21px; height: 21px; stroke-width: 2; }
    .dashboard-empty-state .lead { margin-bottom: 0; color: #5f6b7a; font-size: 14px; }
    .dashboard-period-summary { display: flex; align-items: center; gap: .65rem; min-height: 44px; padding: .65rem .9rem; border: 1px solid #e3e8f1; border-left: 3px solid #6777ef; border-radius: 6px; background: #f8f9fc; color: #7a869a; font-size: 12px; }
    .dashboard-period-summary svg { width: 16px; height: 16px; color: #6777ef; stroke-width: 2.2; }
    .dashboard-period-summary strong { color: #34395e; font-size: 13px; font-weight: 700; }
    .dashboard-content > .row.dashboard-section { align-items: stretch; }
    @media (max-width: 575.98px) {
        .dashboard-kpi__icon { flex-basis: 44px; width: 44px; height: 44px; }
        .dashboard-kpi__value { font-size: 19px; }
    }
</style>
