<style>
    .flights-page { max-width: 1200px; }
    .flights-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #a855f7 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 12px 40px rgba(79, 70, 229, 0.25);
    }
    .flights-hero h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.35rem; }
    .flights-hero p { margin: 0; opacity: 0.92; font-size: 0.95rem; }
    .flights-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
    .flights-nav a {
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        color: #4b5563;
        background: #fff;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    .flights-nav a:hover { border-color: #6366f1; color: #4f46e5; }
    .flights-nav a.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border-color: transparent;
    }
    /* Same loader as landing page — centered, no full-screen background */
    .flights-preloader.preloader {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        background: transparent !important;
        pointer-events: none;
    }
    .flights-preloader.d-none {
        display: none !important;
    }
    .flights-preloader .loader {
        position: relative;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #053750;
        box-shadow: 0 12px 32px rgba(36, 189, 199, 0.35);
    }
    .flights-preloader .loader span {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: rotate(calc(18deg * var(--i)));
    }
    .flights-preloader .loader span::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 10px;
        height: 10px;
        background: #fff;
        border-radius: 50%;
        transform: scale(0);
        animation: flights-home-loader-dot 2s linear infinite;
        animation-delay: calc(0.1s * var(--i));
    }
    @keyframes flights-home-loader-dot {
        0% { transform: scale(0); }
        10% { transform: scale(1.2); }
        80%, 100% { transform: scale(0); }
    }
    .flights-preloader .loader-plane {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        animation: flights-home-loader-rotating 2s linear infinite;
        animation-delay: -1s;
    }
    @keyframes flights-home-loader-rotating {
        0% { transform: rotate(10deg); }
        100% { transform: rotate(370deg); }
    }
    .flights-preloader .loader-plane::before {
        content: '\f072';
        position: absolute;
        font-family: "Font Awesome 6 Pro", "Font Awesome 6 Free", "Font Awesome 5 Free";
        font-weight: 300;
        top: 53px;
        left: 58px;
        color: #fff;
        font-size: 38px;
        transform: rotate(135deg);
    }
    .flights-ajax-busy .flights-page {
        cursor: progress;
    }
    .flights-page form button[type="submit"] {
        position: relative;
    }
    .flights-btn-loading {
        opacity: 0.95;
        pointer-events: none;
    }
    .flights-btn-loading::after {
        content: '';
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-left: 0.5rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: flights-btn-spin 0.7s linear infinite;
        vertical-align: -2px;
    }
    @keyframes flights-btn-spin {
        to { transform: rotate(360deg); }
    }
    .flight-results-pagination .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    .flight-results-pagination .page-link {
        border-radius: 0.5rem;
        margin: 0 0.15rem;
        color: #4f46e5;
    }
    .flight-results-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-color: transparent;
    }
    .flight-search-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
    }
    .trip-type-tabs {
        display: inline-flex;
        background: #f3f4f6;
        border-radius: 0.5rem;
        padding: 0.25rem;
        margin-bottom: 1.25rem;
    }
    .trip-type-tabs label {
        margin: 0;
        cursor: pointer;
    }
    .trip-type-tabs input { display: none; }
    .trip-type-tabs label span {
        display: block;
        padding: 0.45rem 1.1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
    }
    .trip-type-tabs input:checked + span {
        background: #fff;
        color: #4f46e5;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .flight-field-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        margin-bottom: 0.35rem;
    }
    .airport-picker { position: relative; }
    .airport-picker-input-wrap { position: relative; }
    .airport-picker-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6366f1;
        z-index: 2;
        pointer-events: none;
    }
    .airport-picker-display {
        padding-left: 2.35rem !important;
        font-size: 1rem;
        font-weight: 500;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        min-height: 48px;
    }
    .airport-picker-display:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .airport-picker-list {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        z-index: 1050;
        max-height: 280px;
        overflow-y: auto;
        margin: 0;
        padding: 0.35rem 0;
        list-style: none;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    }
    .airport-picker-item {
        padding: 0.65rem 1rem;
        cursor: pointer;
        display: grid;
        grid-template-columns: 3rem 1fr;
        grid-template-rows: auto auto;
        gap: 0 0.5rem;
        align-items: center;
    }
    .airport-picker-item:hover,
    .airport-picker-item.active {
        background: #eef2ff;
    }
    .airport-picker-item-code {
        grid-row: 1 / span 2;
        font-weight: 700;
        color: #4f46e5;
        font-size: 0.9rem;
    }
    .airport-picker-item-main {
        font-weight: 600;
        color: #111827;
        font-size: 0.9rem;
    }
    .airport-picker-item-sub {
        grid-column: 2;
        font-size: 0.75rem;
        color: #6b7280;
    }
    .airport-picker-empty {
        padding: 0.75rem 1rem;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .flight-swap-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1.5rem;
    }
    .flight-swap-btn:hover { background: #eef2ff; border-color: #6366f1; }
    .popular-routes { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
    .popular-routes button {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        color: #374151;
    }
    .popular-routes button:hover { background: #eef2ff; border-color: #c7d2fe; color: #4f46e5; }
    .flight-result-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .flight-result-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 8px 24px rgba(99, 102, 241, 0.12);
    }
    .carrier-badge {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #4338ca;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .flight-timeline {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .flight-time { font-size: 1.25rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .flight-airport-code { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
    .flight-path {
        flex: 1;
        min-width: 80px;
        text-align: center;
        position: relative;
        padding: 0 0.5rem;
    }
    .flight-path::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        height: 2px;
        background: #e5e7eb;
    }
    .flight-path i {
        position: relative;
        background: #fff;
        padding: 0 0.5rem;
        color: #6366f1;
    }
    .price-block { text-align: right; }
    .price-amount { font-size: 1.5rem; font-weight: 700; color: #111827; }
    .price-currency { font-size: 0.85rem; color: #6b7280; font-weight: 600; }
    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .status-pill.ok { background: #d1fae5; color: #065f46; }
    .status-pill.warn { background: #fef3c7; color: #92400e; }
    .status-pill.err { background: #fee2e2; color: #991b1b; }
    .empty-results {
        text-align: center;
        padding: 3rem 2rem;
        background: #f9fafb;
        border-radius: 1rem;
        border: 1px dashed #e5e7eb;
    }
    .sidebar-section-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        padding: 0.75rem 1.5rem 0.25rem;
        margin-top: 0.5rem;
    }
</style>
