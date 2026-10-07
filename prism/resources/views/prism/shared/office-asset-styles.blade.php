@include('prism.shared.receiving-assets', ['suppressReceivingMessages' => true])
@push('page-css')
<style>
/* Match the Office Head Purchase Requests / My PPMPs card and table styles. */
.asset-page {
    --asset-maroon: #681012;
    --asset-border: #e2e8f0;
    --asset-muted: #64748b;
    --asset-shadow: 0 2px 8px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.04);
    padding: 32px 32px 64px; width: 100%; min-width: 0; flex: 1;
    display: flex; flex-direction: column; gap: 24px; color: #334155;
}
.asset-page-header, .asset-card {
    background: #fff; border: 1px solid var(--asset-border); border-radius: 18px;
    padding: 26px; box-shadow: var(--asset-shadow); min-width: 0;
}
.asset-page-header, .asset-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.asset-page-header > div, .asset-card-head > div { min-width: 0; }
.asset-page .asset-eyebrow { font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: var(--asset-maroon); margin: 0 0 4px; }
.asset-page h1, .asset-page h2 { font-size: 17px; font-weight: 800; color: #0f172a; letter-spacing: -.2px; margin: 0; overflow-wrap: anywhere; }
.asset-page .asset-subtitle { font-size: 13px; color: var(--asset-muted); margin-top: 4px; line-height: 1.65; overflow-wrap: anywhere; }
.asset-card-head { margin-bottom: 22px; }
.asset-page .receiving-note { font-size: 12px; color: var(--asset-muted); margin: 6px 0 0; line-height: 1.65; }
.asset-page a { color: var(--asset-maroon); text-decoration: none; }
.asset-page a:hover { text-decoration: underline; }
.asset-page .asset-button, .asset-registration > summary {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 14px; border: 1px solid var(--asset-border); background: #fff; border-radius: 8px;
    color: #475569; font: inherit; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap;
    transition: background .15s, border-color .15s, color .15s;
}
.asset-page .asset-button:hover, .asset-registration > summary:hover { background: rgba(104,16,18,.06); border-color: rgba(104,16,18,.2); color: var(--asset-maroon); text-decoration: none; }
.asset-page .asset-button-small { padding: 6px 10px; }
.asset-page .asset-text-action { display: inline-flex; gap: 6px; align-items: center; font-size: 11px; font-weight: 700; }
.asset-count { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; border: 1px solid rgba(104,16,18,.12); color: var(--asset-maroon); background: rgba(104,16,18,.05); padding: 6px 11px; border-radius: 99px; white-space: nowrap; }
.asset-summary { display: grid; grid-template-columns: repeat(5,minmax(0,1fr)); gap: 13px; }
.asset-stat { position: relative; background: #fff; border: 1px solid var(--asset-border); border-radius: 15px; padding: 16px 18px; min-height: 125px; box-shadow: 0 1px 3px rgba(15,23,42,.07); }
.asset-stat::before { content: ''; position: absolute; left: 0; top: 24px; width: 4px; height: 35px; background: var(--asset-maroon); border-radius: 0 4px 4px 0; }
.asset-stat-icon { position: absolute; right: 14px; top: 14px; width: 32px; height: 32px; background: rgba(104,16,18,.07); border-radius: 10px; display: grid; place-items: center; color: var(--asset-maroon); font-size: 18px; }
.asset-stat-label { display: block; min-height: 30px; padding-right: 33px; font-size: 9px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #64748b; margin-bottom: 8px; }
.asset-stat strong { display: block; font-size: 28px; font-weight: 800; color: var(--asset-maroon); letter-spacing: -.7px; line-height: 1; margin-bottom: 7px; }
.asset-stat-hint { font-size: 11px; color: var(--asset-muted); line-height: 1.5; }
.asset-page .receiving-scroll { border: 1px solid var(--asset-border); border-radius: 12px; overflow-x: auto; }
.asset-page .receiving-table { width: 100%; min-width: 850px; border-collapse: collapse; font-size: 12px; }
.asset-page .receiving-table th { padding: 12px 16px; font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--asset-muted); background: #f8fafc; border-bottom: 1px solid var(--asset-border); }
.asset-page .receiving-table td { padding: 14px 16px; border-bottom: 1px solid var(--asset-border); color: #475569; vertical-align: middle; }
.asset-page .receiving-table td strong { color: #0f172a; font-weight: 600; }
.asset-page .receiving-table tbody tr:last-child td { border-bottom: 0; }
.asset-page .receiving-table tbody tr:hover td { background: rgba(104,16,18,.025); }
.asset-page #received-items td:nth-child(n+3):nth-child(-n+6) { white-space: nowrap; }
.asset-page small { display: block; font-size: 10.5px; color: var(--asset-muted); margin-top: 4px; line-height: 1.6; }
.asset-pill { display: inline-flex; align-items: center; border-radius: 99px; padding: 4px 10px; font-size: 10px; font-weight: 700; background: #f1f5f9; color: #475569; white-space: nowrap; border: 1px solid var(--asset-border); }
.asset-pill[data-status="In Use"], .asset-pill[data-status="Under Warranty"] { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
.asset-pill[data-status="Expiring Soon"], .asset-pill[data-status="Under Repair"] { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.asset-pill[data-status="Warranty Expired"] { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
.asset-page .receiving-form { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; padding: 20px; margin: 14px 0 0; background: #f8fafc; border: 1px solid var(--asset-border); border-radius: 12px; }
.asset-page .receiving-form label { font-size: 11px; font-weight: 600; color: #475569; gap: 7px; min-width: 0; }
.asset-page .receiving-form input:not([type=checkbox]), .asset-page .receiving-form select, .asset-page .receiving-form textarea { width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid var(--asset-border); border-radius: 8px; background: white; color: #334155; font: inherit; font-size: 12px; box-sizing: border-box; }
.asset-page .receiving-form input::placeholder { color: #94a3b8; }
.asset-page :is(input, select, textarea, button, a, summary):focus-visible { outline: 2px solid #8b1a1c; outline-offset: 3px; }
.asset-page .receiving-form button { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; border-radius: 8px; font: inherit; font-size: 11px; font-weight: 700; background: var(--asset-maroon); color: white; cursor: pointer; }
.asset-page .receiving-form button:hover:not(:disabled) { background: #4e0c0e; }
.asset-page button:disabled { opacity: .5; cursor: not-allowed; }
.asset-page .asset-filters { grid-template-columns: repeat(4,minmax(0,1fr)); padding: 0; margin: 0; background: none; border: 0; }
.asset-filters .asset-search { grid-column: span 2; }
.asset-registration > summary { list-style: none; }
.asset-registration > summary::-webkit-details-marker { display: none; }
.asset-registration[open] { min-width: 250px; }
.asset-page .asset-registration .receiving-form { grid-template-columns: minmax(0,1fr); padding: 14px; }
.asset-registration input[type=checkbox], .asset-check { accent-color: var(--asset-maroon); }
.asset-check { width: 17px; height: 17px; cursor: pointer; }
.asset-page .receiving-table .asset-empty { padding: 36px 20px; text-align: center; color: var(--asset-muted); font-size: 12px; }
.asset-empty > i { display: block; font-size: 28px; color: #94a3b8; margin-bottom: 12px; }
.asset-empty > strong { display: block; font-size: 13px; margin-bottom: 6px; }
.asset-pagination { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 18px; font-size: 11px; color: var(--asset-muted); }
.asset-pagination a { font-weight: 700; }
.asset-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.asset-bulk-heading { display: flex; align-items: center; gap: 8px; padding-top: 22px; margin-top: 20px; border-top: 1px solid var(--asset-border); font-size: 12px; font-weight: 700; color: var(--asset-maroon); }
.asset-disclosure { border: 1px solid var(--asset-border); border-radius: 10px; margin-top: 12px; overflow: hidden; }
.asset-disclosure > summary { display: flex; justify-content: space-between; align-items: center; gap: 12px; list-style: none; padding: 14px 16px; font-size: 12px; font-weight: 700; color: #334155; background: #f8fafc; cursor: pointer; }
.asset-disclosure > summary::-webkit-details-marker { display: none; }
.asset-disclosure[open] > summary > i { transform: rotate(180deg); }
.asset-page .asset-disclosure > .receiving-form { border: 0; border-top: 1px solid var(--asset-border); border-radius: 0; margin: 0; background: white; }
.asset-detail-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 20px 24px; margin: 0 0 24px; }
.asset-detail-grid > div { padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; min-width: 0; }
.asset-detail-grid dt { font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--asset-muted); margin-bottom: 7px; }
.asset-detail-grid dd { margin: 0; color: #0f172a; font-size: 12px; font-weight: 600; overflow-wrap: anywhere; }
.asset-page .receiving-message { margin: 0; padding: 14px 18px; border-radius: 10px; font-size: 12px; }
.asset-history { border-left: 3px solid #c9a84c; padding: 12px 18px; margin-top: 16px; background: #f8fafc; border-radius: 0 8px 8px 0; overflow-wrap: anywhere; }
.asset-history > strong { font-size: 12px; color: #0f172a; }
.asset-history p { font-size: 12px; margin: 8px 0; }
.asset-history summary { font-size: 11px; font-weight: 700; color: var(--asset-maroon); cursor: pointer; }
.asset-history table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 11px; }
.asset-history td, .asset-history th { text-align: left; padding: 8px; border-bottom: 1px solid var(--asset-border); overflow-wrap: anywhere; }
.asset-page .asset-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; }
.asset-page .asset-tabs a { border: 1px solid var(--asset-border); border-radius: 8px; padding: 10px 14px; background: white; font-size: 12px; font-weight: 600; text-decoration: none; }
.asset-page .asset-tabs [aria-current=page] { background: var(--asset-maroon); color: white; border-color: var(--asset-maroon); }
@media(max-width:1280px) { .asset-summary { grid-template-columns: repeat(3,minmax(0,1fr)); } }
@media(max-width:900px) { .asset-page { padding: 24px 20px 48px; gap: 20px; } .asset-summary, .asset-detail-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } .asset-page .asset-filters { grid-template-columns: repeat(2,minmax(0,1fr)); } }
@media(max-width:600px) { .asset-page { padding: 16px 14px 36px; gap: 16px; } .asset-page-header, .asset-card { padding: 18px; border-radius: 14px; } .asset-stat { padding: 14px; } .asset-stat-label { font-size: 8.5px; letter-spacing: .06em; } .asset-stat-icon { right: 10px; width: 28px; height: 28px; } .asset-page .receiving-form, .asset-page .asset-filters, .asset-detail-grid { grid-template-columns: minmax(0,1fr); } .asset-filters .asset-search { grid-column: auto; } .asset-card-head { margin-bottom: 16px; } }
</style>
@endpush
