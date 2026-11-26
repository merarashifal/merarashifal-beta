UPDATE af_snippets SET code = '/**
 * Dasha Calculator Engine (V3 Modular Shortcodes)
 * Dedicated Shortcodes:
 *   [dasha_current_mahadasha] - Current Mahadasha & Antardasha
 *   [dasha_mahadasha_full]    - Maha Dasha Full Report
 *   [dasha_predictions]       - Mahadasha Predictions
 *   [dasha_antardasha]        - Antardasha Matrix
 *   [dasha_current_sadesati]  - Current Sade Sati & Remedies
 *   [dasha_sadesati_table]    - Sade Sati Table
 *   [dasha_landing]           - Landing Hub Directory
 * Unified Shortcode:
 *   [dasha_calc_engine tool="..."] or [dasha_calc tool="..."]
 * Features:
 *   - Form & Result on same page
 *   - 4 Master Reports Cross-Sell
 *   - Harmonized Warm Amber Theme (#FFFBEB, #FDE68A, #B45309, #78350F)
 *   - 100% Mobile Responsive with zero-gutter overrides
 *   - Google Places City Autocomplete with .mr-city-loader
 */

if (!defined(''ABSPATH'')) exit;

/* ====================================================
1. SHARED STYLES FOR DASHA ENGINE & CROSS-SELL
==================================================== */
function mr_dasha_print_shared_styles() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <style>
    :root {
        --mr-dasha-bg: #FFFBEB;
        --mr-dasha-card-bg: #FFFFFF;
        --mr-dasha-border: #FDE68A;
        --mr-dasha-border-focus: #FCD34D;
        --mr-dasha-primary: #B45309;
        --mr-dasha-primary-hover: #92400E;
        --mr-dasha-amber-dark: #78350F;
        --mr-dasha-text: #1E293B;
        --mr-dasha-muted: #64748B;
        --mr-dasha-shadow: 0 4px 18px -2px rgba(180, 83, 9, 0.07);
    }

    .mr-dasha-wrap {
        font-family: ''Noto Sans'', ''Noto Sans Devanagari'', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: var(--mr-dasha-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 16px;
        padding: 24px;
        margin: 15px auto 40px auto;
        max-width: 1050px;
        box-sizing: border-box;
        color: var(--mr-dasha-text);
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-dasha-wrap * { box-sizing: border-box; }

    /* Override Elementor Gutters on Mobile */
    @media (max-width: 680px) {
        .elementor-element.elementor-element-2b58b0d,
        .elementor-3532 .e-con-boxed,
        .elementor-3532 .e-con-full,
        .elementor-3532 .elementor-element,
        .e-con-boxed, .e-con-full {
            padding-left: 2px !important;
            padding-right: 2px !important;
        }
        .mr-dasha-wrap {
            padding: 12px 8px !important;
            margin: 4px 0 35px 0 !important;
            border-radius: 12px !important;
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    /* City Loader Ring */
    .mr-city-loader {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 20px !important;
        height: 20px !important;
        pointer-events: none !important;
        z-index: 15 !important;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .mr-city-loader.is-active { display: inline-flex !important; }
    .mr-city-spinner-ring {
        width: 18px !important;
        height: 18px !important;
        border: 2.2px solid rgba(217, 119, 6, 0.22) !important;
        border-top-color: #D97706 !important;
        border-right-color: #B45309 !important;
        border-radius: 50% !important;
        display: inline-block !important;
        box-sizing: border-box !important;
        animation: mrCitySpinRing 0.65s linear infinite !important;
    }
    @keyframes mrCitySpinRing {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Header & Badge */
    .mr-dasha-header {
        text-align: center;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 1px dashed var(--mr-dasha-border);
    }
    .mr-dasha-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #FEF3C7;
        color: var(--mr-dasha-amber-dark);
        border: 1px solid var(--mr-dasha-border);
        font-size: 12px;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 999px;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mr-dasha-header h1, .mr-dasha-header h2 {
        font-size: 24px !important;
        font-weight: 800 !important;
        color: var(--mr-dasha-amber-dark) !important;
        margin: 0 0 8px 0 !important;
        line-height: 1.3;
    }
    @media (min-width: 768px) {
        .mr-dasha-header h1, .mr-dasha-header h2 { font-size: 28px !important; }
    }
    .mr-dasha-header p {
        font-size: 14px;
        color: var(--mr-dasha-muted);
        margin: 0;
        line-height: 1.5;
        max-width: 750px;
        margin: 0 auto;
    }

    /* Landing Tools Grid */
    .mr-dasha-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 680px) {
        .mr-dasha-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    .mr-dasha-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 20px;
        text-decoration: none !important;
        color: inherit;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(180, 83, 9, 0.04);
    }
    .mr-dasha-card:hover {
        transform: translateY(-3px);
        border-color: #D97706;
        box-shadow: 0 8px 22px rgba(180, 83, 9, 0.12);
        background: #FFFDF9;
    }
    .mr-dasha-card-top {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 12px;
    }
    .mr-dasha-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #FEF3C7;
        border: 1px solid var(--mr-dasha-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .mr-dasha-card-title-wrap h3 {
        font-size: 17px !important;
        font-weight: 800 !important;
        color: var(--mr-dasha-amber-dark) !important;
        margin: 0 0 3px 0 !important;
        line-height: 1.3;
    }
    .mr-dasha-card-sub {
        font-size: 13px;
        color: var(--mr-dasha-primary);
        font-weight: 600;
        margin: 0;
    }
    .mr-dasha-card-desc {
        font-size: 13.5px;
        color: #475569;
        line-height: 1.55;
        margin: 0 0 16px 0;
        flex-grow: 1;
    }
    .mr-dasha-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px dashed var(--mr-dasha-border);
    }
    .mr-dasha-btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        background: #FEF3C7;
        color: var(--mr-dasha-amber-dark) !important;
        border: 1px solid var(--mr-dasha-border);
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .mr-dasha-card:hover .mr-dasha-btn-pill {
        background: var(--mr-dasha-primary);
        color: #FFFFFF !important;
        border-color: var(--mr-dasha-primary);
    }

    /* Back Bar / Navigation Header */
    .mr-dasha-nav-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--mr-dasha-border);
    }
    .mr-dasha-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--mr-dasha-primary);
        font-size: 13.5px;
        font-weight: 700;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: color 0.15s;
    }
    .mr-dasha-back-btn:hover {
        color: var(--mr-dasha-primary-hover);
        text-decoration: underline;
    }

    /* Hero Banner for Form */
    .mr-dasha-hero-card {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        box-shadow: 0 2px 10px rgba(180, 83, 9, 0.05);
    }
    .mr-dasha-hero-icon {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        background: #FFFFFF;
        border: 1px solid var(--mr-dasha-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.08);
    }
    .mr-dasha-hero-text {
        flex: 1;
        min-width: 220px;
    }
    .mr-dasha-hero-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        margin: 0 0 6px 0;
        line-height: 1.3;
    }
    .mr-dasha-hero-desc {
        font-size: 13.5px;
        color: #334155;
        line-height: 1.55;
        margin: 0;
    }

    /* Form Container */
    .mr-dasha-form-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-dasha-form-title {
        font-size: 19px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        margin: 0 0 4px 0;
    }
    .mr-dasha-form-sub {
        font-size: 13px;
        color: var(--mr-dasha-muted);
        margin: 0 0 20px 0;
    }
    .mr-dasha-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }
    @media (max-width: 600px) {
        .mr-dasha-form-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .mr-dasha-form-card {
            padding: 16px 12px;
        }
    }
    .mr-dasha-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .mr-dasha-field.full {
        grid-column: 1 / -1;
    }
    .mr-dasha-label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--mr-dasha-amber-dark);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mr-dasha-input, .mr-dasha-select {
        width: 100%;
        height: 46px;
        padding: 0 14px;
        background: #FFFDF7;
        border: 1.5px solid var(--mr-dasha-border-focus);
        border-radius: 10px;
        font-size: 14.5px;
        color: var(--mr-dasha-text);
        outline: none;
        transition: all 0.2s ease;
    }
    .mr-dasha-input:focus, .mr-dasha-select:focus {
        border-color: var(--mr-dasha-primary);
        box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.15);
        background: #FFFFFF;
    }
    .mr-dasha-hint {
        font-size: 11.5px;
        color: var(--mr-dasha-muted);
        margin-top: 2px;
    }
    .mr-dasha-submit-btn {
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, #B45309 0%, #D97706 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(180, 83, 9, 0.25);
        transition: all 0.2s ease;
        margin-top: 10px;
    }
    .mr-dasha-submit-btn:hover {
        background: linear-gradient(135deg, #92400E 0%, #B45309 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(180, 83, 9, 0.35);
    }
    .mr-dasha-submit-btn:active {
        transform: translateY(0);
    }

    /* Result Cards & Grids */
    .mr-dasha-res-header {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 22px;
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-dasha-res-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        font-size: 13px;
        color: var(--mr-dasha-amber-dark);
        font-weight: 600;
        margin-bottom: 8px;
    }
    .mr-dasha-res-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(253, 230, 138, 0.6);
        padding: 3px 10px;
        border-radius: 6px;
    }
    .mr-dasha-res-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        margin: 4px 0 0 0;
        line-height: 1.3;
    }

    /* Active Dasha Grid (Cards) */
    .mr-dasha-level-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (max-width: 600px) {
        .mr-dasha-level-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    .mr-dasha-level-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 12px;
        padding: 16px;
        box-shadow: var(--mr-dasha-shadow);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .mr-dasha-level-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .mr-dasha-level-tag {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 3px 8px;
        border-radius: 4px;
        background: #FEF3C7;
        color: var(--mr-dasha-amber-dark);
    }
    .mr-dasha-level-planet {
        font-size: 20px;
        font-weight: 800;
        color: var(--mr-dasha-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mr-dasha-level-dates {
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed var(--mr-dasha-border);
        font-size: 12.5px;
        color: #475569;
        display: flex;
        justify-content: space-between;
    }

    /* Predictions Cards */
    .mr-dasha-pred-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-dasha-pred-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #FEF3C7;
    }
    .mr-dasha-pred-planet {
        font-size: 18px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mr-dasha-pred-body {
        font-size: 14px;
        color: #334155;
        line-height: 1.65;
    }

    /* Sade Sati Verdict Banner */
    .mr-sadesati-verdict-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 22px;
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-sadesati-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: 999px;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mr-sadesati-status-active {
        background: #FEF2F2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }
    .mr-sadesati-status-inactive {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
    }
    .mr-sadesati-metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin: 16px 0;
    }
    .mr-sadesati-metric-box {
        background: #FFFDF7;
        border: 1px solid var(--mr-dasha-border);
        border-radius: 10px;
        padding: 12px 14px;
    }
    .mr-sadesati-metric-lbl {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--mr-dasha-muted);
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .mr-sadesati-metric-val {
        font-size: 15px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
    }
    .mr-sadesati-analysis-box {
        background: #FEF3C7;
        border-left: 4px solid var(--mr-dasha-primary);
        border-radius: 0 10px 10px 0;
        padding: 14px 18px;
        margin: 16px 0;
        font-size: 14px;
        line-height: 1.6;
        color: var(--mr-dasha-amber-dark);
    }
    .mr-remedies-list {
        margin: 12px 0 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .mr-remedy-item {
        background: #FFFDF7;
        border: 1px solid var(--mr-dasha-border);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 13.5px;
        line-height: 1.55;
        color: #334155;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .mr-remedy-item span.icon {
        color: #16A34A;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Sade Sati Responsive Table */
    .mr-dasha-table-wrap {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--mr-dasha-shadow);
    }
    .mr-dasha-table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .mr-dasha-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
        text-align: left;
    }
    .mr-dasha-table thead {
        background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
        border-bottom: 2px solid var(--mr-dasha-border);
    }
    .mr-dasha-table th {
        padding: 12px 14px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        white-space: nowrap;
        font-size: 12.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mr-dasha-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #F1F5F9;
        color: #334155;
        vertical-align: middle;
    }
    .mr-dasha-table tbody tr:hover {
        background-color: #FFFDF7;
    }
    .mr-dasha-badge-retro {
        display: inline-block;
        background: #FEF2F2;
        color: #DC2626;
        border: 1px solid #FECACA;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }
    .mr-dasha-badge-dir {
        display: inline-block;
        background: #FEF3C7;
        color: var(--mr-dasha-primary);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11.5px;
        font-weight: 700;
    }

    /* Section Subheaders */
    .mr-dasha-section-heading {
        font-size: 18px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        margin: 24px 0 14px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ====================================================
       MASTER REPORTS CROSS-SELL (Harmonized 4-Card Module)
       ==================================================== */
    .mr-crosssell-wrap {
        margin-top: 36px;
        padding-top: 24px;
        border-top: 2px dashed var(--mr-dasha-border);
    }
    .mr-crosssell-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }
    .mr-crosssell-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mr-crosssell-badge {
        background: #FEF3C7;
        border: 1px solid var(--mr-dasha-border);
        color: var(--mr-dasha-primary);
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mr-crosssell-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    @media (max-width: 900px) {
        .mr-crosssell-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
    }
    @media (max-width: 480px) {
        .mr-crosssell-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }
    .mr-crosssell-card {
        background: var(--mr-dasha-card-bg);
        border: 1px solid var(--mr-dasha-border);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none !important;
        transition: all 0.25s ease;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.05);
    }
    .mr-crosssell-card:hover {
        transform: translateY(-3px);
        border-color: #D97706;
        box-shadow: 0 8px 18px rgba(180, 83, 9, 0.12);
        background: #FFFDF9;
    }
    .mr-crosssell-img-box {
        position: relative;
        background: #FFFBEB;
        border-bottom: 1px solid var(--mr-dasha-border);
        padding: 10px;
        text-align: center;
        aspect-ratio: 16 / 10;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .mr-crosssell-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    .mr-crosssell-card:hover .mr-crosssell-img {
        transform: scale(1.05);
    }
    .mr-crosssell-content {
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
    }
    .mr-crosssell-tags {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
    }
    .mr-crosssell-tag-discount {
        background: #DC2626;
        color: #FFFFFF;
        font-size: 10.5px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
    }
    .mr-crosssell-tag-pages {
        background: #FEF3C7;
        color: var(--mr-dasha-amber-dark);
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
    }
    .mr-crosssell-name {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--mr-dasha-amber-dark);
        line-height: 1.35;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .mr-crosssell-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 8px;
        border-top: 1px dashed var(--mr-dasha-border);
    }
    .mr-crosssell-price-row {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    .mr-crosssell-price {
        font-size: 17px;
        font-weight: 800;
        color: #16A34A;
    }
    .mr-crosssell-mrp {
        font-size: 11.5px;
        color: #94A3B8;
        text-decoration: line-through;
    }
    .mr-crosssell-btn {
        background: #FEF3C7;
        color: var(--mr-dasha-amber-dark);
        border: 1px solid var(--mr-dasha-border);
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .mr-crosssell-card:hover .mr-crosssell-btn {
        background: var(--mr-dasha-primary);
        color: #FFFFFF;
        border-color: var(--mr-dasha-primary);
    }
    @media (max-width: 480px) {
        .mr-crosssell-content { padding: 8px 10px; }
        .mr-crosssell-name { font-size: 12px; margin-bottom: 6px; }
        .mr-crosssell-price { font-size: 15px; }
        .mr-crosssell-btn { padding: 3px 8px; font-size: 10.5px; }
    }
    </style>
    <?php
}

/* ====================================================
2. TOOL DEFINITIONS & METADATA
==================================================== */
function mr_dasha_get_tools_catalog() {
    return [
        ''current-mahadasha'' => [
            ''icon''        => ''🪐'',
            ''title_en''    => ''Current Mahadasha & Antardasha'',
            ''title_hi''    => ''वर्तमान महादशा एवं अंतर्दशा'',
            ''desc_en''     => ''Know your active Mahadasha, Antardasha, and governing planetary periods influencing your current phase of life.'',
            ''desc_hi''     => ''वर्तमान में सक्रिय महादशा, अंतर्दशा एवं जीवन पर प्रभाव डालने वाले प्रमुख ग्रह काल की सटीक स्थिति जानें।'',
            ''btn_en''      => ''Calculate Mahadasha'',
            ''btn_hi''      => ''महादशा निकालें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/dashas/current-mahadasha''
        ],
        ''current-mahadasha-full'' => [
            ''icon''        => ''📜'',
            ''title_en''    => ''Maha Dasha Full Report (120 Years)'',
            ''title_hi''    => ''पूर्ण महादशा रिपोर्ट (120 वर्ष)'',
            ''desc_en''     => ''Get a complete 120-year lifetime timeline of all Mahadashas and their sub-periods from birth to old age.'',
            ''desc_hi''     => ''जन्म से लेकर वृद्धावस्था तक जीवन के सभी 120 वर्षों की महादशाओं और उप-दशाओं का संपूर्ण कालक्रम देखें।'',
            ''btn_en''      => ''View Full Report'',
            ''btn_hi''      => ''पूरी रिपोर्ट देखें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/dashas/current-mahadasha-full''
        ],
        ''maha-dasha-predictions'' => [
            ''icon''        => ''🔮'',
            ''title_en''    => ''Mahadasha Predictions & Effects'',
            ''title_hi''    => ''महादशा भविष्यफल एवं प्रभाव'',
            ''desc_en''     => ''Read detailed astrological predictions and planetary effects for career, finances, health, and family life.'',
            ''desc_hi''     => ''करियर, धन, स्वास्थ्य, वैवाहिक जीवन और पारिवारिक सुख पर प्रमुख ग्रह दशाओं के विस्तृत ज्योतिषीय फलित पढ़ें।'',
            ''btn_en''      => ''Read Predictions'',
            ''btn_hi''      => ''भविष्यफल पढ़ें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/dashas/maha-dasha-predictions''
        ],
        ''antar-dasha'' => [
            ''icon''        => ''🧭'',
            ''title_en''    => ''Antardasha Timeline & Matrix'',
            ''title_hi''    => ''अंतर्दशा टाइमलाइन एवं चक्र'',
            ''desc_en''     => ''Explore the complete matrix of Antardasha sub-periods, transition dates, and planetary timing within each Mahadasha.'',
            ''desc_hi''     => ''प्रत्येक महादशा के भीतर चलने वाली 9 अंतर्दशाओं, उनकी आरंभ तिथियों और समय चक्र का विस्तृत विवरण देखें।'',
            ''btn_en''      => ''Calculate Antardasha'',
            ''btn_hi''      => ''अंतर्दशा निकालें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/dashas/antar-dasha''
        ],
        ''current-sade-sati'' => [
            ''icon''        => ''⚖️'',
            ''title_en''    => ''Current Shani Sade Sati Status'',
            ''title_hi''    => ''वर्तमान शनि साढ़े साती स्थिति'',
            ''desc_en''     => ''Check if you are under the influence of Shani Sade Sati or Dhaiya, and discover authentic Vedic remedies.'',
            ''desc_hi''     => ''जानें कि क्या आप वर्तमान में शनि की साढ़े साती या ढैया के प्रभाव में हैं, और जानें अचूक वैदिक उपाय।'',
            ''btn_en''      => ''Check Sade Sati'',
            ''btn_hi''      => ''साढ़े साती जांचें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/extended-horoscope/current-sade-sati''
        ],
        ''sade-sati-table'' => [
            ''icon''        => ''📊'',
            ''title_en''    => ''Lifetime Sade Sati Transit Table'',
            ''title_hi''    => ''साढ़े साती संपूर्ण गोचर तालिका'',
            ''desc_en''     => ''View the comprehensive chronological transit table of all past and future Sade Sati and Dhaiya cycles.'',
            ''desc_hi''     => ''जीवन के सभी भूत, वर्तमान और भविष्य के साढ़े साती एवं ढैया चरणों की विस्तृत गोचर तालिका देखें।'',
            ''btn_en''      => ''View Transit Table'',
            ''btn_hi''      => ''तालिका देखें'',
            ''endpoint''    => ''https://api.vedicastroapi.com/v3-json/extended-horoscope/sade-sati-table''
        ]
    ];
}

/* ====================================================
3. MASTER REPORTS CROSS-SELL DISPLAY FUNCTION
==================================================== */
function mr_dasha_display_cross_sell($lang = ''en'') {
    $header_title = ($lang === ''hi'') ? ''⚡ आपके लिए अनुशंसित मास्टर ज्योतिष रिपोर्ट्स'' : ''⚡ Recommended Master Reports for You'';
    $badge_text   = ($lang === ''hi'') ? ''सटीक जन्मकुंडली विश्लेषण'' : ''Personalized Vedic Readings'';
    $get_btn_text = ($lang === ''hi'') ? ''रिपोर्ट पाएं &rarr;'' : ''Get Report &rarr;'';

    $is_local = (isset($_SERVER[''HTTP_HOST'']) && strpos($_SERVER[''HTTP_HOST''], ''localhost'') !== false);
    $base_url = $is_local ? home_url(''/reports/'') : ''https://merarashifal.com/reports/'';

    $products = [
        [
            ''title''     => ($lang === ''hi'') ? ''मास्टर वैदिक दशा लाइफ टाइमिंग रिपोर्ट'' : ''Master Vedic Dasha Life Timing Report'',
            ''url''       => $base_url . ''dasha-timing-report/'',
            ''img_id''    => 7396,
            ''img_file''  => ''/wp-content/uploads/2026/11/vedic-dasha-timing-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹999'',
            ''discount''  => ''90% OFF'',
            ''pages''     => ''26 Pages''
        ],
        [
            ''title''     => ($lang === ''hi'') ? ''मास्टर जन्म कुंडली एवं हार्मोनिक्स रिपोर्ट'' : ''Master Janma Kundali & Harmonics Report'',
            ''url''       => $base_url . ''janam-kundali-master-report/'',
            ''img_id''    => 7398,
            ''img_file''  => ''/wp-content/uploads/2026/11/janma-kundali-harmonics-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹999'',
            ''discount''  => ''90% OFF'',
            ''pages''     => ''26 Pages''
        ],
        [
            ''title''     => ($lang === ''hi'') ? ''मास्टर कुंडली मिलान (36 गुण मिलान) रिपोर्ट'' : ''Master Kundali Matching (Gun Milan) Report'',
            ''url''       => $base_url . ''kundali-and-gun-matching-report/'',
            ''img_id''    => 7394,
            ''img_file''  => ''/wp-content/uploads/2026/11/kundali-matching-gun-milan-report.webp'',
            ''sale_price''=> ''₹99'',
            ''mrp''       => ''₹1,499'',
            ''discount''  => ''93% OFF'',
            ''pages''     => ''24 Pages''
        ],
        [
            ''title''     => ($lang === ''hi'') ? ''मास्टर हस्तरेखा एवं पामिस्ट्री विश्लेषण रिपोर्ट'' : ''Master Hast Rekha & Palmistry Analysis Report'',
            ''url''       => $base_url . ''palm-reading-report/'',
            ''img_id''    => 7399,
            ''img_file''  => ''/wp-content/uploads/2026/11/Master-Palmistry-Analysis-Report.webp'',
            ''sale_price''=> ''₹299'',
            ''mrp''       => ''₹1,499'',
            ''discount''  => ''80% OFF'',
            ''pages''     => ''26 Pages''
        ]
    ];
    ?>
    <div class="mr-crosssell-wrap">
        <div class="mr-crosssell-header">
            <h3 class="mr-crosssell-title"><?php echo esc_html($header_title); ?></h3>
            <span class="mr-crosssell-badge"><?php echo esc_html($badge_text); ?></span>
        </div>
        <div class="mr-crosssell-grid">
            <?php foreach ($products as $p):
                $img_src = '''';
                if (!empty($p[''img_id''])) {
                    $img_src = wp_get_attachment_image_url($p[''img_id''], ''medium'') ?: wp_get_attachment_url($p[''img_id'']);
                }
                if (!$img_src) {
                    $img_src = home_url($p[''img_file'']);
                }
            ?>
                <a href="<?php echo esc_url($p[''url'']); ?>" class="mr-crosssell-card" target="_blank" rel="noopener">
                    <div class="mr-crosssell-img-box">
                        <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($p[''title'']); ?>" class="mr-crosssell-img" loading="lazy">
                    </div>
                    <div class="mr-crosssell-content">
                        <div>
                            <div class="mr-crosssell-tags">
                                <span class="mr-crosssell-tag-discount"><?php echo esc_html($p[''discount'']); ?></span>
                                <span class="mr-crosssell-tag-pages"><?php echo esc_html($p[''pages'']); ?></span>
                            </div>
                            <div class="mr-crosssell-name"><?php echo esc_html($p[''title'']); ?></div>
                        </div>
                        <div class="mr-crosssell-bottom">
                            <div class="mr-crosssell-price-row">
                                <span class="mr-crosssell-price"><?php echo esc_html($p[''sale_price'']); ?></span>
                                <span class="mr-crosssell-mrp"><?php echo esc_html($p[''mrp'']); ?></span>
                            </div>
                            <div class="mr-crosssell-btn"><?php echo $get_btn_text; ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/* ====================================================
4. DATE FORMAT HELPER
==================================================== */
function mr_dasha_format_date($date_str) {
    if (!$date_str) return '''';
    $clean_str = preg_replace(''/GMT\\+\\d{4}\\s*\\([^)]+\\)/'', '''', $date_str);
    $clean_str = trim($clean_str);
    $ts = strtotime($clean_str);
    if ($ts) {
        if (strpos($date_str, '':'') !== false) {
            return date(''d M Y, H:i'', $ts);
        }
        return date(''d M Y'', $ts);
    }
    return esc_html($date_str);
}

/* ====================================================
5. FORM RENDERER
==================================================== */
function mr_dasha_render_form($tool, $defaults = []) {
    $catalog = mr_dasha_get_tools_catalog();
    $meta    = $catalog[$tool] ?? $catalog[''current-mahadasha''];

    $dob   = $defaults[''dob''] ?? '''';
    $tob   = $defaults[''tob''] ?? ''12:00'';
    $place = $defaults[''place''] ?? '''';
    $lat   = $defaults[''lat''] ?? '''';
    $lon   = $defaults[''lon''] ?? '''';
    $tz    = $defaults[''tz''] ?? ''5.5'';
    $lang  = $defaults[''lang''] ?? ''en'';

    ?>
    <div class="mr-dasha-hero-card">
        <div class="mr-dasha-hero-icon"><?php echo $meta[''icon'']; ?></div>
        <div class="mr-dasha-hero-text">
            <h2 class="mr-dasha-hero-title"><?php echo esc_html($meta[''title_en'']); ?></h2>
            <p class="mr-dasha-hero-desc"><?php echo esc_html($meta[''desc_en'']); ?><br><span style="color: var(--mr-dasha-primary); font-size: 13px; font-weight: 600;"><?php echo esc_html($meta[''title_hi'']); ?>: <?php echo esc_html($meta[''desc_hi'']); ?></span></p>
        </div>
    </div>

    <div class="mr-dasha-form-card">
        <h3 class="mr-dasha-form-title">Enter Birth Details | जन्म विवरण दर्ज करें</h3>
        <p class="mr-dasha-form-sub">Accurate birth time and city coordinates are required for planetary period calculations.</p>

        <form method="POST" action="" class="mr-dasha-form">
            <input type="hidden" name="mr_dasha_action" value="calculate">
            <input type="hidden" name="mr_dasha_tool" value="<?php echo esc_attr($tool); ?>">
            <input type="hidden" name="lat" id="mr_dasha_lat" value="<?php echo esc_attr($lat); ?>">
            <input type="hidden" name="lon" id="mr_dasha_lon" value="<?php echo esc_attr($lon); ?>">
            <input type="hidden" name="tz"  id="mr_dasha_tz"  value="<?php echo esc_attr($tz); ?>">

            <div class="mr-dasha-form-grid">
                <div class="mr-dasha-field">
                    <label class="mr-dasha-label">Date of Birth / जन्म तिथि *</label>
                    <input type="date" name="dob" class="mr-dasha-input" value="<?php echo esc_attr($dob); ?>" required>
                    <span class="mr-dasha-hint">Select your date of birth</span>
                </div>

                <div class="mr-dasha-field">
                    <label class="mr-dasha-label">Time of Birth / जन्म समय *</label>
                    <input type="time" name="tob" class="mr-dasha-input" value="<?php echo esc_attr($tob); ?>" required>
                    <span class="mr-dasha-hint">24-hour format (e.g. 14:30)</span>
                </div>

                <div class="mr-dasha-field full">
                    <label class="mr-dasha-label">Place of Birth / जन्म स्थान *</label>
                    <div style="position: relative;">
                        <input type="text" name="place" id="mr_dasha_place" class="mr-dasha-input js-city-autocomplete" data-places="city" placeholder="Type birth city (e.g. New Delhi, Delhi, India)" value="<?php echo esc_attr($place); ?>" autocomplete="off" required>
                        <span class="mr-city-loader"><span class="mr-city-spinner-ring"></span></span>
                    </div>
                    <span class="mr-dasha-hint">Start typing city name and pick from the dropdown list</span>
                </div>

                <div class="mr-dasha-field full">
                    <label class="mr-dasha-label">Report Language / भाषा</label>
                    <div style="display: flex; gap: 20px; align-items: center; margin-top: 4px;">
                        <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                            <input type="radio" name="lang" value="en" <?php checked($lang, ''en''); ?>> English (EN)
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                            <input type="radio" name="lang" value="hi" <?php checked($lang, ''hi''); ?>> हिन्दी (HI)
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="mr-dasha-submit-btn">
                <span><?php echo esc_html($meta[''btn_en'']); ?></span> &rarr;
            </button>
        </form>
    </div>

    <!-- Master Reports Cross-Sell -->
    <?php mr_dasha_display_cross_sell($lang); ?>
    <?php
}

/* ====================================================
6. RESULT RENDERER
==================================================== */
function mr_dasha_render_result($tool, $params) {
    $dob_raw = $params[''dob'']; // YYYY-MM-DD
    $tob     = $params[''tob''];
    $place   = $params[''place''];
    $lat     = $params[''lat''];
    $lon     = $params[''lon''];
    $tz      = $params[''tz''];
    $lang    = $params[''lang''] ?? ''en'';

    // Format DOB to DD/MM/YYYY for API
    $dob_parts = explode(''-'', $dob_raw);
    if (count($dob_parts) === 3) {
        $dob_api = "{$dob_parts[2]}/{$dob_parts[1]}/{$dob_parts[0]}";
        $dob_display = "{$dob_parts[2]}/{$dob_parts[1]}/{$dob_parts[0]}";
    } else {
        $dob_api = $dob_raw;
        $dob_display = $dob_raw;
    }

    $catalog = mr_dasha_get_tools_catalog();
    $meta    = $catalog[$tool] ?? $catalog[''current-mahadasha''];

    $api_key = defined(''VEDICASTRO_API_KEY'') ? VEDICASTRO_API_KEY : ''8d9d828d-3f9b-531a-a640-88c00469c300'';
    $api_args = [
        ''dob''     => $dob_api,
        ''tob''     => $tob,
        ''lat''     => $lat,
        ''lon''     => $lon,
        ''tz''      => $tz,
        ''api_key'' => $api_key,
        ''lang''    => $lang
    ];

    $api_url = add_query_arg($api_args, $meta[''endpoint'']);
    $resp = wp_remote_get($api_url, [''timeout'' => 35]);

    if (is_wp_error($resp)) {
        ?>
        <div class="mr-dasha-form-card" style="text-align: center; border-color: #FECACA;">
            <h3 style="color: #991B1B;">Connection Error | सर्वर कनेक्शन त्रुटि</h3>
            <p><?php echo esc_html($resp->get_error_message()); ?></p>
            <form method="POST" action="" style="display: inline-block; margin-top: 15px;">
                <input type="hidden" name="dob" value="<?php echo esc_attr($dob_raw); ?>">
                <input type="hidden" name="tob" value="<?php echo esc_attr($tob); ?>">
                <input type="hidden" name="place" value="<?php echo esc_attr($place); ?>">
                <input type="hidden" name="lat" value="<?php echo esc_attr($lat); ?>">
                <input type="hidden" name="lon" value="<?php echo esc_attr($lon); ?>">
                <input type="hidden" name="tz" value="<?php echo esc_attr($tz); ?>">
                <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>">
                <button type="submit" class="mr-dasha-submit-btn" style="width: auto; padding: 0 24px;">&larr; Try Again</button>
            </form>
        </div>
        <?php
        return;
    }

    $body = wp_remote_retrieve_body($resp);
    $json = json_decode($body, true);
    $res  = $json[''response''] ?? null;

    if (!$res) {
        ?>
        <div class="mr-dasha-form-card" style="text-align: center; border-color: #FECACA;">
            <h3 style="color: #991B1B;">Calculation Service Busy | डेटा उपलब्ध नहीं</h3>
            <p>Could not retrieve astronomical calculation data. Please check birth parameters and try again.</p>
            <form method="POST" action="" style="display: inline-block; margin-top: 15px;">
                <input type="hidden" name="dob" value="<?php echo esc_attr($dob_raw); ?>">
                <input type="hidden" name="tob" value="<?php echo esc_attr($tob); ?>">
                <input type="hidden" name="place" value="<?php echo esc_attr($place); ?>">
                <input type="hidden" name="lat" value="<?php echo esc_attr($lat); ?>">
                <input type="hidden" name="lon" value="<?php echo esc_attr($lon); ?>">
                <input type="hidden" name="tz" value="<?php echo esc_attr($tz); ?>">
                <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>">
                <button type="submit" class="mr-dasha-submit-btn" style="width: auto; padding: 0 24px;">&larr; Re-enter Birth Data</button>
            </form>
        </div>
        <?php
        return;
    }

    $planet_glyphs = [
        ''Sun''     => ''☉ Sun'',     ''Surya''   => ''☉ सूर्य'',   ''सूर्य''   => ''☉ सूर्य'',
        ''Moon''    => ''☽ Moon'',    ''Chandra'' => ''☽ चन्द्र'',  ''चन्द्र''  => ''☽ चन्द्र'',
        ''Mars''    => ''♂ Mars'',    ''Mangal''  => ''♂ मंगल'',   ''मंगल''   => ''♂ मंगल'',
        ''Mercury'' => ''☿ Mercury'', ''Budha''   => ''☿ बुध'',    ''बुध''    => ''☿ बुध'',
        ''Jupiter'' => ''♃ Jupiter'', ''Guru''    => ''♃ गुरु'',    ''बृहस्पति''=> ''♃ बृहस्पति'', ''गुरु'' => ''♃ गुरु'',
        ''Venus''   => ''♀ Venus'',   ''Shukra''  => ''♀ शुक्र'',   ''शुक्र''   => ''♀ शुक्र'',
        ''Saturn''  => ''♄ Saturn'',  ''Shani''   => ''♄ शनि'',    ''शनि''    => ''♄ शनि'',
        ''Rahu''    => ''☊ Rahu'',    ''राहु''    => ''☊ राहु'',
        ''Ketu''    => ''☋ Ketu'',    ''केतु''    => ''☋ केतु''
    ];

    $alt_lang = ($lang === ''hi'') ? ''en'' : ''hi'';
    $alt_lang_label = ($lang === ''hi'') ? ''🇬🇧 View in English'' : ''🇮🇳 हिन्दी में देखें'';
    ?>
    <div class="mr-dasha-nav-bar">
        <!-- Recalculate Button (Back to Form) -->
        <a href="<?php echo esc_url(remove_query_arg([''mr_dasha_action''])); ?>" class="mr-dasha-back-btn" onclick="document.getElementById(''mr_dasha_change_form'').submit(); return false;">
            &larr; <?php echo ($lang === ''hi'') ? ''जन्म विवरण बदलें / नया विवरण'' : ''Change Birth Details''; ?>
        </a>

        <!-- Form for Change Details with Prefilled Values -->
        <form id="mr_dasha_change_form" method="POST" action="" style="display: none;">
            <input type="hidden" name="dob" value="<?php echo esc_attr($dob_raw); ?>">
            <input type="hidden" name="tob" value="<?php echo esc_attr($tob); ?>">
            <input type="hidden" name="place" value="<?php echo esc_attr($place); ?>">
            <input type="hidden" name="lat" value="<?php echo esc_attr($lat); ?>">
            <input type="hidden" name="lon" value="<?php echo esc_attr($lon); ?>">
            <input type="hidden" name="tz" value="<?php echo esc_attr($tz); ?>">
            <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>">
        </form>

        <!-- Instant Language Switcher Form -->
        <form method="POST" action="" style="display: inline-block; margin: 0;">
            <input type="hidden" name="mr_dasha_action" value="calculate">
            <input type="hidden" name="mr_dasha_tool" value="<?php echo esc_attr($tool); ?>">
            <input type="hidden" name="dob" value="<?php echo esc_attr($dob_raw); ?>">
            <input type="hidden" name="tob" value="<?php echo esc_attr($tob); ?>">
            <input type="hidden" name="place" value="<?php echo esc_attr($place); ?>">
            <input type="hidden" name="lat" value="<?php echo esc_attr($lat); ?>">
            <input type="hidden" name="lon" value="<?php echo esc_attr($lon); ?>">
            <input type="hidden" name="tz" value="<?php echo esc_attr($tz); ?>">
            <input type="hidden" name="lang" value="<?php echo esc_attr($alt_lang); ?>">
            <button type="submit" class="mr-dasha-btn-pill" style="cursor: pointer; border: 1px solid var(--mr-dasha-border);">
                <?php echo esc_html($alt_lang_label); ?>
            </button>
        </form>
    </div>

    <!-- Result Header -->
    <div class="mr-dasha-res-header">
        <div class="mr-dasha-res-meta">
            <span class="mr-dasha-res-meta-item">📅 <?php echo esc_html($dob_display); ?></span>
            <span class="mr-dasha-res-meta-item">⏰ <?php echo esc_html($tob); ?></span>
            <?php if ($place): ?>
                <span class="mr-dasha-res-meta-item">📍 <?php echo esc_html($place); ?></span>
            <?php endif; ?>
            <span class="mr-dasha-res-meta-item">🌐 <?php echo strtoupper(esc_html($lang)); ?></span>
        </div>
        <h1 class="mr-dasha-res-title"><?php echo esc_html($lang === ''hi'' ? $meta[''title_hi''] : $meta[''title_en'']); ?></h1>
    </div>

    <?php
    /* ----------------------------------------------------
       SUB-VIEW 1: CURRENT MAHADASHA
       ---------------------------------------------------- */
    if ($tool === ''current-mahadasha'') {
        $levels = [
            ''mahadasha''        => ($lang === ''hi'') ? ''महादशा'' : ''Mahadasha'',
            ''antardasha''       => ($lang === ''hi'') ? ''अंतर्दशा'' : ''Antardasha'',
            ''paryantardasha''   => ($lang === ''hi'') ? ''प्रत्यंतर्दशा'' : ''Paryantardasha'',
            ''Shookshamadasha''  => ($lang === ''hi'') ? ''सूक्ष्मदशा'' : ''Sookshma Dasha'',
            ''Pranadasha''       => ($lang === ''hi'') ? ''प्राणदशा'' : ''Prana Dasha''
        ];
        echo ''<div class="mr-dasha-section-heading">🪐 '' . ($lang === ''hi'' ? ''सक्रिय ग्रह दशा स्तर (Active Dasha Levels)'' : ''Active Planetary Dasha Hierarchy'') . ''</div>'';
        echo ''<div class="mr-dasha-level-grid">'';
        foreach ($levels as $key => $label):
            $d = $res[$key] ?? null;
            if (!$d) continue;
            $p_name = $d[''name''] ?? '''';
            $p_key  = $d[''key''] ?? $p_name;
            $glyph  = $planet_glyphs[$p_name] ?? ($planet_glyphs[$p_key] ?? $p_name);
            ?>
            <div class="mr-dasha-level-card">
                <div>
                    <div class="mr-dasha-level-top">
                        <span class="mr-dasha-level-tag"><?php echo esc_html($label); ?></span>
                    </div>
                    <div class="mr-dasha-level-planet"><?php echo esc_html($glyph); ?></div>
                </div>
                <div class="mr-dasha-level-dates">
                    <span><strong>From:</strong> <?php echo mr_dasha_format_date($d[''start''] ?? ''''); ?></span>
                    <span><strong>To:</strong> <?php echo mr_dasha_format_date($d[''end''] ?? ''''); ?></span>
                </div>
            </div>
            <?php
        endforeach;
        echo ''</div>'';

    /* ----------------------------------------------------
       SUB-VIEW 2: MAHADASHA FULL REPORT
       ---------------------------------------------------- */
    } elseif ($tool === ''current-mahadasha-full'') {
        echo ''<div class="mr-dasha-section-heading">📜 '' . ($lang === ''hi'' ? ''संपूर्ण 120 वर्षीय जीवन महादशा चक्र'' : ''Complete 120-Year Lifetime Mahadasha Timeline'') . ''</div>'';
        echo ''<div class="mr-dasha-level-grid">'';
        if (is_array($res)) {
            foreach ($res as $item) {
                if (!is_array($item)) continue;
                $p_name = $item[''name''] ?? ($item[''planet''] ?? '''');
                $glyph  = $planet_glyphs[$p_name] ?? $p_name;
                ?>
                <div class="mr-dasha-level-card">
                    <div>
                        <div class="mr-dasha-level-top">
                            <span class="mr-dasha-level-tag">Planetary Period</span>
                        </div>
                        <div class="mr-dasha-level-planet"><?php echo esc_html($glyph); ?></div>
                    </div>
                    <div class="mr-dasha-level-dates">
                        <span><strong>From:</strong> <?php echo mr_dasha_format_date($item[''start''] ?? ''''); ?></span>
                        <span><strong>To:</strong> <?php echo mr_dasha_format_date($item[''end''] ?? ''''); ?></span>
                    </div>
                </div>
                <?php
            }
        }
        echo ''</div>'';

    /* ----------------------------------------------------
       SUB-VIEW 3: MAHADASHA PREDICTIONS
       ---------------------------------------------------- */
    } elseif ($tool === ''maha-dasha-predictions'') {
        echo ''<div class="mr-dasha-section-heading">🔮 '' . ($lang === ''hi'' ? ''ग्रह दशा फलित एवं ज्योतिषीय प्रभाव'' : ''Astrological Predictions & Period Effects'') . ''</div>'';
        if (is_array($res)) {
            foreach ($res as $k => $item) {
                if (!is_array($item)) continue;
                $planet = $item[''planet''] ?? ($item[''name''] ?? $k);
                $glyph  = $planet_glyphs[$planet] ?? $planet;
                $pred   = $item[''prediction''] ?? ($item[''desc''] ?? ($item[''description''] ?? ''''));
                if (!$pred && isset($item[''text''])) $pred = $item[''text''];
                ?>
                <div class="mr-dasha-pred-card">
                    <div class="mr-dasha-pred-header">
                        <div class="mr-dasha-pred-planet"><?php echo esc_html($glyph); ?></div>
                        <span class="mr-dasha-level-tag">Planetary Influence</span>
                    </div>
                    <div class="mr-dasha-pred-body">
                        <?php echo nl2br(esc_html($pred)); ?>
                    </div>
                </div>
                <?php
            }
        }

    /* ----------------------------------------------------
       SUB-VIEW 4: ANTARDASHA MATRIX
       ---------------------------------------------------- */
    } elseif ($tool === ''antar-dasha'') {
        echo ''<div class="mr-dasha-section-heading">🧭 '' . ($lang === ''hi'' ? ''अंतर्दशा उप-काल विवरण'' : ''Antardasha Sub-period Matrix'') . ''</div>'';
        echo ''<div class="mr-dasha-level-grid">'';
        if (is_array($res)) {
            foreach ($res as $item) {
                if (!is_array($item)) continue;
                $name = $item[''name''] ?? ($item[''planet''] ?? ''Sub-period'');
                ?>
                <div class="mr-dasha-level-card">
                    <div>
                        <div class="mr-dasha-level-top">
                            <span class="mr-dasha-level-tag">Antardasha</span>
                        </div>
                        <div class="mr-dasha-level-planet"><?php echo esc_html($name); ?></div>
                    </div>
                    <div class="mr-dasha-level-dates">
                        <span><strong>From:</strong> <?php echo mr_dasha_format_date($item[''start''] ?? ''''); ?></span>
                        <span><strong>To:</strong> <?php echo mr_dasha_format_date($item[''end''] ?? ''''); ?></span>
                    </div>
                </div>
                <?php
            }
        }
        echo ''</div>'';

    /* ----------------------------------------------------
       SUB-VIEW 5: CURRENT SADE SATI
       ---------------------------------------------------- */
    } elseif ($tool === ''current-sade-sati'') {
        $is_active   = !empty($res[''is_sade_sati_period'']);
        $period_type = $res[''shani_period_type''] ?? '''';
        $date_cons   = $res[''date_considered''] ?? '''';
        $bot_resp    = $res[''bot_response''] ?? '''';
        $desc        = $res[''description''] ?? '''';
        $retro       = !empty($res[''saturn_retrograde'']);
        $age         = $res[''age''] ?? '''';
        $remedies    = $res[''remedies''] ?? [];

        ?>
        <div class="mr-sadesati-verdict-card">
            <?php if ($is_active): ?>
                <div class="mr-sadesati-status-pill mr-sadesati-status-active">
                    ⚠️ <?php echo ($lang === ''hi'') ? "शनि साढ़े साती सक्रिय ({$period_type})" : "Shani Sade Sati Currently Active ({$period_type})"; ?>
                </div>
            <?php else: ?>
                <div class="mr-sadesati-status-pill mr-sadesati-status-inactive">
                    ✅ <?php echo ($lang === ''hi'') ? ''साढ़े साती सक्रिय नहीं है (No Active Sade Sati)'' : ''No Active Sade Sati at Present''; ?>
                </div>
            <?php endif; ?>

            <div class="mr-sadesati-metrics-grid">
                <div class="mr-sadesati-metric-box">
                    <div class="mr-sadesati-metric-lbl"><?php echo ($lang === ''hi'') ? ''विचारित तिथि'' : ''Date Considered''; ?></div>
                    <div class="mr-sadesati-metric-val"><?php echo esc_html($date_cons ?: ''Current''); ?></div>
                </div>
                <div class="mr-sadesati-metric-box">
                    <div class="mr-sadesati-metric-lbl"><?php echo ($lang === ''hi'') ? ''ढैया / चरण'' : ''Phase / Period Type''; ?></div>
                    <div class="mr-sadesati-metric-val"><?php echo esc_html($period_type ?: ''None''); ?></div>
                </div>
                <div class="mr-sadesati-metric-box">
                    <div class="mr-sadesati-metric-lbl"><?php echo ($lang === ''hi'') ? ''शनि वक्री स्थिति'' : ''Saturn Retrograde''; ?></div>
                    <div class="mr-sadesati-metric-val">
                        <?php if ($retro): ?>
                            <span style="color:#DC2626;">[R] Yes (Retrograde)</span>
                        <?php else: ?>
                            <span style="color:#16A34A;">No (Direct)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($age): ?>
                <div class="mr-sadesati-metric-box">
                    <div class="mr-sadesati-metric-lbl"><?php echo ($lang === ''hi'') ? ''वर्तमान आयु'' : ''Current Age''; ?></div>
                    <div class="mr-sadesati-metric-val"><?php echo esc_html($age); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($bot_resp): ?>
                <div class="mr-sadesati-analysis-box">
                    <div style="font-weight: 800; margin-bottom: 4px;">🪐 <?php echo ($lang === ''hi'') ? ''ज्योतिषीय विश्लेषण:'' : ''Astrological Analysis:''; ?></div>
                    <?php echo esc_html($bot_resp); ?>
                </div>
            <?php endif; ?>

            <?php if ($desc): ?>
                <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #475569;">
                    <strong>ℹ️ <?php echo ($lang === ''hi'') ? ''साढ़े साती के बारे में:'' : ''About Sade Sati:''; ?></strong>
                    <?php echo esc_html($desc); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($remedies) && is_array($remedies)): ?>
                <div class="mr-dasha-section-heading" style="margin-top: 24px;">🛡️ <?php echo ($lang === ''hi'') ? ''निर्धारित अचूक वैदिक उपाय'' : ''Prescribed Vedic Remedies''; ?></div>
                <ul class="mr-remedies-list">
                    <?php foreach ($remedies as $rem): ?>
                        <li class="mr-remedy-item">
                            <span class="icon">✅</span>
                            <span><?php echo esc_html($rem); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php

    /* ----------------------------------------------------
       SUB-VIEW 6: SADE SATI TABLE
       ---------------------------------------------------- */
    } elseif ($tool === ''sade-sati-table'') {
        echo ''<div class="mr-dasha-section-heading">📊 '' . ($lang === ''hi'' ? ''जीवन पर्यंत साढ़े साती एवं ढैया गोचर चक्र'' : ''Lifetime Sade Sati & Dhaiya Transit Table'') . ''</div>'';
        if (is_array($res) && !empty($res)):
            ?>
            <div class="mr-dasha-table-wrap">
                <div class="mr-dasha-table-scroll">
                    <table class="mr-dasha-table">
                        <thead>
                            <tr>
                                <th><?php echo ($lang === ''hi'') ? ''प्रकार'' : ''Type''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''ढैया चरण'' : ''Dhaiya Phase''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''गोचर राशि'' : ''Transit Zodiac''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''आरंभ तिथि'' : ''Start Date''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''समाप्ति तिथि'' : ''End Date''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''गति'' : ''Direction''; ?></th>
                                <th><?php echo ($lang === ''hi'') ? ''वक्री स्थिति'' : ''Retrograde''; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($res as $row):
                                if (!is_array($row)) continue;
                                $is_retro = !empty($row[''is_retrograde'']);
                            ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--mr-dasha-amber-dark);"><?php echo esc_html($row[''type''] ?? ''Sade Sati''); ?></td>
                                <td><?php echo esc_html($row[''summary''] ?? ($row[''phase''] ?? ''—'')); ?></td>
                                <td><strong><?php echo esc_html($row[''transit_zodiac''] ?? ($row[''sign''] ?? ''—'')); ?></strong></td>
                                <td><?php echo mr_dasha_format_date($row[''start_date''] ?? ''''); ?></td>
                                <td><?php echo mr_dasha_format_date($row[''end_date''] ?? ''''); ?></td>
                                <td>
                                    <span class="mr-dasha-badge-dir">
                                        <?php echo esc_html($row[''direction''] ?? ($is_retro ? ''Retrograde'' : ''Direct'')); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_retro): ?>
                                        <span class="mr-dasha-badge-retro">[R] Retrograde</span>
                                    <?php else: ?>
                                        <span style="color:#16A34A; font-weight:700;">Direct</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        endif;
    }
    ?>

    <!-- Master Reports Cross-Sell -->
    <?php mr_dasha_display_cross_sell($lang); ?>
    <?php
}

/* ====================================================
7. LANDING HUB DIRECTORY RENDERER
==================================================== */
function mr_dasha_render_landing_hub() {
    $catalog = mr_dasha_get_tools_catalog();
    ?>
    <div class="mr-dasha-header">
        <div class="mr-dasha-pill">🪐 वैदिक दशा एवं साढ़े साती • Vedic Dasha System</div>
        <h1>दशा एवं साढ़े साती कैलकुलेटर | Kundali Dasha Calculator</h1>
        <p>Calculate your active Mahadasha, Antardasha timeline, Shani Sade Sati status, and personalized Vedic astrological predictions based on authentic birth chart calculations.</p>
    </div>

    <div class="mr-dasha-grid">
        <?php foreach ($catalog as $slug => $item): ?>
        <a href="<?php echo esc_url(home_url("/dasha/{$slug}/")); ?>" class="mr-dasha-card">
            <div>
                <div class="mr-dasha-card-top">
                    <div class="mr-dasha-card-icon"><?php echo $item[''icon'']; ?></div>
                    <div class="mr-dasha-card-title-wrap">
                        <h3><?php echo esc_html($item[''title_en'']); ?></h3>
                        <div class="mr-dasha-card-sub"><?php echo esc_html($item[''title_hi'']); ?></div>
                    </div>
                </div>
                <p class="mr-dasha-card-desc"><?php echo esc_html($item[''desc_en'']); ?><br><span style="color: #64748B; font-size: 12.5px;"><?php echo esc_html($item[''desc_hi'']); ?></span></p>
            </div>
            <div class="mr-dasha-card-footer">
                <span style="font-size: 12px; font-weight: 600; color: #64748B;">Vedic Astrology</span>
                <span class="mr-dasha-btn-pill"><?php echo esc_html($item[''btn_en'']); ?> &rarr;</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Master Reports Cross-Sell -->
    <?php mr_dasha_display_cross_sell(''en''); ?>
    <?php
}

/* ====================================================
8. UNIVERSAL CONTROLLER (FORM + RESULT DISPATCHER)
==================================================== */
function mr_dasha_render_tool_shortcode($tool = ''current-mahadasha'') {
    ob_start();
    mr_dasha_print_shared_styles();

    if ($tool === ''landing'') {
        echo ''<div class="mr-dasha-wrap">'';
        mr_dasha_render_landing_hub();
        echo ''</div>'';
        return ob_get_clean();
    }

    echo ''<div class="mr-dasha-wrap">'';

    // Check if form was submitted
    $is_submitted = (
        isset($_POST[''mr_dasha_action'']) && 
        $_POST[''mr_dasha_action''] === ''calculate'' &&
        !empty($_POST[''dob''])
    );

    if ($is_submitted) {
        $params = [
            ''dob''   => sanitize_text_field($_POST[''dob'']),
            ''tob''   => sanitize_text_field($_POST[''tob''] ?? ''12:00''),
            ''place'' => sanitize_text_field($_POST[''place''] ?? ''''),
            ''lat''   => floatval($_POST[''lat''] ?? 28.6139),
            ''lon''   => floatval($_POST[''lon''] ?? 77.2090),
            ''tz''    => floatval($_POST[''tz''] ?? 5.5),
            ''lang''  => sanitize_text_field($_POST[''lang''] ?? ''en'')
        ];
        mr_dasha_render_result($tool, $params);
    } else {
        // Prefilled values if passed back from "Change Details"
        $defaults = [
            ''dob''   => sanitize_text_field($_POST[''dob''] ?? ''''),
            ''tob''   => sanitize_text_field($_POST[''tob''] ?? ''12:00''),
            ''place'' => sanitize_text_field($_POST[''place''] ?? ''''),
            ''lat''   => sanitize_text_field($_POST[''lat''] ?? ''''),
            ''lon''   => sanitize_text_field($_POST[''lon''] ?? ''''),
            ''tz''    => sanitize_text_field($_POST[''tz''] ?? ''5.5''),
            ''lang''  => sanitize_text_field($_POST[''lang''] ?? ''en'')
        ];
        mr_dasha_render_form($tool, $defaults);
    }

    echo ''</div>'';
    return ob_get_clean();
}

/* ====================================================
9. SHORTCODE REGISTRATIONS
==================================================== */

// 1. Dedicated Shortcode: Current Mahadasha & Antardasha
add_shortcode(''dasha_current_mahadasha'', function() {
    return mr_dasha_render_tool_shortcode(''current-mahadasha'');
});

// 2. Dedicated Shortcode: Maha Dasha Full Report
add_shortcode(''dasha_mahadasha_full'', function() {
    return mr_dasha_render_tool_shortcode(''current-mahadasha-full'');
});

// 3. Dedicated Shortcode: Mahadasha Predictions
add_shortcode(''dasha_predictions'', function() {
    return mr_dasha_render_tool_shortcode(''maha-dasha-predictions'');
});

// 4. Dedicated Shortcode: Antardasha Matrix
add_shortcode(''dasha_antardasha'', function() {
    return mr_dasha_render_tool_shortcode(''antar-dasha'');
});

// 5. Dedicated Shortcode: Current Sade Sati
add_shortcode(''dasha_current_sadesati'', function() {
    return mr_dasha_render_tool_shortcode(''current-sade-sati'');
});

// 6. Dedicated Shortcode: Sade Sati Table
add_shortcode(''dasha_sadesati_table'', function() {
    return mr_dasha_render_tool_shortcode(''sade-sati-table'');
});

// 7. Dedicated Shortcode: Landing Page Hub
add_shortcode(''dasha_landing'', function() {
    return mr_dasha_render_tool_shortcode(''landing'');
});

// 8. Unified Shortcode: [dasha_calc_engine tool="..."] or [dasha_calc tool="..."]
add_shortcode(''dasha_calc_engine'', function($atts) {
    $a = shortcode_atts([
        ''type'' => '''',
        ''tool'' => ''''
    ], $atts);

    $chosen_tool = $a[''tool''] ?: $a[''type''];

    if (!$chosen_tool) {
        // If on landing or no type specified, render landing hub
        return mr_dasha_render_tool_shortcode(''landing'');
    }
    return mr_dasha_render_tool_shortcode($chosen_tool);
});

add_shortcode(''dasha_calc'', function($atts) {
    $a = shortcode_atts([
        ''type'' => ''current-mahadasha'',
        ''tool'' => ''current-mahadasha''
    ], $atts);
    $chosen_tool = !empty($atts[''type'']) ? $atts[''type''] : $a[''tool''];
    return mr_dasha_render_tool_shortcode($chosen_tool);
});
', active = 1, scope = 'global' WHERE id = 70;
