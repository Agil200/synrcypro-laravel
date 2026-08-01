<style>
    .ccsp-page {
        --ccsp-red: #d71920;
        --ccsp-red-dark: #b31319;
        --ccsp-border: #dce2e8;
        --ccsp-text: #172033;
        --ccsp-muted: #687386;
        --ccsp-dark: #241819;
        display: grid;
        gap: 14px;
        min-width: 0;
    }

    .ccsp-card {
        overflow: hidden;
        border: 1px solid var(--ccsp-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .ccsp-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--ccsp-border);
    }

    .ccsp-title {
        margin: 0 0 5px;
        color: var(--ccsp-text);
        font-size: 21px;
        font-weight: 800;
    }

    .ccsp-subtitle {
        margin: 0;
        color: var(--ccsp-muted);
        font-size: 13px;
        line-height: 1.45;
    }

    .ccsp-primary,
    .ccsp-secondary,
    .ccsp-action,
    .ccsp-page-link,
    .ccsp-form-cancel,
    .ccsp-form-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-family: inherit;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition:
            background .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .ccsp-primary {
        min-height: 40px;
        gap: 7px;
        padding: 0 15px;
        border: 0;
        color: #fff;
        background: var(--ccsp-red);
        font-size: 13px;
    }

    .ccsp-primary:hover {
        transform: translateY(-1px);
        background: var(--ccsp-red-dark);
    }

    .ccsp-toolbar {
        display: grid;
        grid-template-columns:
            minmax(220px, 330px)
            minmax(220px, 1fr)
            repeat(2, minmax(120px, 160px));
        gap: 12px;
        align-items: end;
        padding: 16px 20px;
    }

    .ccsp-field {
        display: grid;
        gap: 7px;
    }

    .ccsp-field label {
        color: #30394a;
        font-size: 12px;
        font-weight: 800;
    }

    .ccsp-input,
    .ccsp-select,
    .ccsp-textarea {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        border: 1px solid #cfd6de;
        border-radius: 8px;
        outline: none;
        color: var(--ccsp-text);
        background: #fff;
        font-family: inherit;
        font-size: 13px;
    }

    .ccsp-textarea {
        min-height: 92px;
        resize: vertical;
    }

    .ccsp-input:focus,
    .ccsp-select:focus,
    .ccsp-textarea:focus {
        border-color: var(--ccsp-red);
        box-shadow: 0 0 0 3px rgba(215, 25, 32, .1);
    }

    .ccsp-stat {
        min-height: 70px;
        padding: 11px 14px;
        border: 1px solid var(--ccsp-border);
        border-radius: 9px;
        background: #f8fafc;
    }

    .ccsp-stat span {
        display: block;
        margin-bottom: 5px;
        color: var(--ccsp-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .ccsp-stat strong {
        color: var(--ccsp-text);
        font-size: 21px;
        font-weight: 900;
    }

    .ccsp-alert {
        margin: 14px 20px 0;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
    }

    .ccsp-success {
        border: 1px solid #b7dfc5;
        color: #166534;
        background: #ecfdf3;
    }

    .ccsp-error {
        border: 1px solid #f0b4b7;
        color: #991b1b;
        background: #fff1f2;
    }

    .ccsp-error ul {
        margin: 7px 0 0;
        padding-left: 18px;
    }

    .ccsp-table-wrap {
        overflow-x: auto;
        padding: 0 20px 16px;
    }

    .ccsp-table {
        width: 100%;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
        color: #293244;
        font-size: 12px;
    }

    .ccsp-table th,
    .ccsp-table td {
        padding: 11px 10px;
        border-bottom: 1px solid #e5e9ee;
        text-align: left;
        vertical-align: middle;
    }

    .ccsp-table th {
        background: #f5f7f9;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .ccsp-file {
        display: inline-flex;
        max-width: 180px;
        align-items: center;
        gap: 6px;
        color: #1468c3;
        font-weight: 800;
        text-decoration: none;
    }

    .ccsp-file span:last-child {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ccsp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .ccsp-action {
        min-height: 31px;
        padding: 0 10px;
        border: 1px solid #d4dae1;
        color: #283244;
        background: #fff;
        font-size: 11px;
    }

    .ccsp-danger {
        border-color: #f0b4b7;
        color: #b91c1c;
        background: #fff5f5;
    }

    .ccsp-empty {
        padding: 40px 20px !important;
        color: var(--ccsp-muted);
        text-align: center !important;
    }

    .ccsp-badge {
        display: inline-flex;
        min-height: 24px;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
    }

    .ccsp-badge.active {
        color: #166534;
        background: #dcfce7;
    }

    .ccsp-badge.expired {
        color: #991b1b;
        background: #fee2e2;
    }

    .ccsp-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 13px 20px 16px;
        color: var(--ccsp-muted);
        font-size: 12px;
    }

    .ccsp-pagination {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ccsp-page-link {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border: 1px solid #d8dee5;
        color: #30394a;
        background: #fff;
    }

    .ccsp-disabled {
        opacity: .45;
        pointer-events: none;
    }

    /* ==========================================================
       FORM MODAL — meniru tampilan referensi yang diberikan
       ========================================================== */

    .ccsp-modal {
        position: fixed;
        z-index: 1200;
        inset: 0;
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding: 14px;
        overflow-y: auto;
        background: rgba(17, 24, 39, .60);
    }

    .ccsp-modal.is-open {
        display: flex;
    }

    .ccsp-dialog {
        width: min(760px, 100%);
        margin: auto;
        overflow: hidden;
        border: 1px solid #d6d9de;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
    }

    .ccsp-reference-toolbar {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        min-height: 58px;
        padding: 0 14px;
        border-bottom: 1px solid #e3e5e8;
        background: #fff;
    }

    .ccsp-reference-close {
        display: inline-grid;
        width: 30px;
        height: 30px;
        place-items: center;
        padding: 0;
        border: 0;
        color: #70757b;
        background: transparent;
        font-size: 28px;
        font-weight: 300;
        line-height: 1;
        cursor: pointer;
    }

    .ccsp-reference-title {
        margin: 0;
        color: #172033;
        font-size: 17px;
        font-weight: 500;
        letter-spacing: .01em;
    }

    .ccsp-reference-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ccsp-form-cancel,
    .ccsp-form-save {
        min-height: 34px;
        padding: 0 14px;
        border-radius: 4px;
        font-size: 12px;
    }

    .ccsp-form-cancel {
        border: 1px solid #8b8b8b;
        color: #292929;
        background: #fff;
    }

    .ccsp-form-save {
        border: 1px solid var(--ccsp-dark);
        color: #fff;
        background: var(--ccsp-dark);
    }

    .ccsp-page-tabbar {
        position: relative;
        display: flex;
        min-height: 54px;
        align-items: flex-end;
        justify-content: center;
        border-bottom: 1px solid #dedede;
        background: #fff;
    }

    .ccsp-page-tab {
        position: relative;
        min-width: 145px;
        padding: 0 20px 16px;
        color: #222;
        font-size: 12px;
        text-align: center;
    }

    .ccsp-page-tab::after {
        position: absolute;
        right: 0;
        bottom: -1px;
        left: 0;
        height: 4px;
        border-radius: 4px 4px 0 0;
        background: var(--ccsp-dark);
        content: "";
    }

    .ccsp-reference-body {
        width: min(100%, 650px);
        margin: 0 auto;
        padding: 28px 28px 42px;
    }

    .ccsp-reference-row {
        display: grid;
        grid-template-columns: 138px minmax(0, 1fr);
        gap: 20px;
        align-items: center;
        margin-bottom: 18px;
    }

    .ccsp-reference-row.is-top {
        align-items: flex-start;
    }

    .ccsp-reference-label {
        color: #4b5363;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .ccsp-reference-control {
        min-width: 0;
    }

    .ccsp-reference-input,
    .ccsp-reference-select,
    .ccsp-reference-textarea {
        width: 100%;
        min-height: 43px;
        padding: 9px 12px;
        border: 1px solid #bfc6cf;
        border-radius: 4px;
        outline: 0;
        color: #222;
        background: #fff;
        font-family: inherit;
        font-size: 14px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .ccsp-reference-textarea {
        min-height: 76px;
        resize: vertical;
    }

    .ccsp-reference-input:focus,
    .ccsp-reference-select:focus,
    .ccsp-reference-textarea:focus {
        border-color: #2c2223;
        box-shadow: 0 0 0 2px rgba(44, 34, 35, .08);
    }

    .ccsp-reference-input[disabled],
    .ccsp-reference-input[readonly] {
        color: #a8aeb7;
        border-color: #d9dde2;
        background: #fbfbfb;
    }

    .ccsp-reference-input-group {
        position: relative;
        display: flex;
        width: 100%;
    }

    .ccsp-reference-input-group .ccsp-reference-input,
    .ccsp-reference-input-group .ccsp-reference-select {
        padding-right: 48px;
    }

    .ccsp-reference-addon {
        position: absolute;
        top: 1px;
        right: 1px;
        bottom: 1px;
        display: grid;
        width: 44px;
        place-items: center;
        border: 0;
        border-left: 1px solid transparent;
        color: #6b6b6b;
        background: #fff;
        font-size: 26px;
        font-weight: 300;
        line-height: 1;
        pointer-events: none;
    }

    .ccsp-reference-select {
        appearance: none;
        padding-right: 42px;
        background-image:
            linear-gradient(45deg, transparent 50%, #777 50%),
            linear-gradient(135deg, #777 50%, transparent 50%);
        background-position:
            calc(100% - 18px) 18px,
            calc(100% - 13px) 18px;
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
    }

    .ccsp-reference-help {
        display: block;
        margin-top: 6px;
        color: #8290a4;
        font-size: 11px;
    }

    .ccsp-reference-upload-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }

    .ccsp-reference-upload {
        display: grid;
        min-height: 62px;
        place-items: center;
        gap: 5px;
        padding: 10px 12px;
        border: 1px solid #bfc6cf;
        border-radius: 4px;
        color: #6b7280;
        background: #fff;
        cursor: pointer;
        text-align: center;
    }

    .ccsp-reference-upload:hover {
        border-color: #2c2223;
        background: #fcfcfc;
    }

    .ccsp-reference-upload-icon {
        color: #62666d;
        font-size: 23px;
        line-height: 1;
    }

    .ccsp-reference-upload-name {
        display: block;
        max-width: 100%;
        overflow: hidden;
        color: #7a8290;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ccsp-reference-error {
        margin: 18px 18px 0;
    }

    body.ccsp-modal-open {
        overflow: hidden;
    }

    @media (max-width: 1050px) {
        .ccsp-toolbar {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 680px) {
        .ccsp-header,
        .ccsp-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .ccsp-toolbar {
            grid-template-columns: 1fr;
        }

        .ccsp-primary {
            width: 100%;
        }

        .ccsp-modal {
            padding: 0;
        }

        .ccsp-dialog {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            border: 0;
            border-radius: 0;
        }

        .ccsp-reference-toolbar {
            grid-template-columns: auto minmax(0, 1fr);
            padding: 8px 10px;
        }

        .ccsp-reference-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
            padding-bottom: 4px;
        }

        .ccsp-reference-body {
            padding: 24px 18px 34px;
        }

        .ccsp-reference-row {
            grid-template-columns: 1fr;
            gap: 7px;
            margin-bottom: 15px;
        }
    }

    /* ==========================================================
       HOTFIX MODAL
       Memastikan form tambah/edit tidak tampil sebagai konten biasa.
       ========================================================== */

    .ccsp-modal[hidden] {
        display: none !important;
    }

    .ccsp-modal {
        position: fixed !important;
        z-index: 99999 !important;
        inset: 0 !important;
        display: none !important;
        width: 100vw !important;
        height: 100vh !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
        padding: 16px !important;
        overflow: hidden !important;
        background: rgba(17, 24, 39, .68) !important;
    }

    .ccsp-modal.is-open:not([hidden]) {
        display: flex !important;
    }

    .ccsp-dialog {
        position: relative !important;
        display: flex !important;
        width: min(760px, calc(100vw - 32px)) !important;
        max-width: 760px !important;
        max-height: calc(100vh - 32px) !important;
        flex-direction: column !important;
        margin: 0 !important;
        overflow: hidden !important;
        border: 1px solid #d6d9de !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .32) !important;
    }

    .ccsp-dialog > form {
        display: flex !important;
        min-width: 0 !important;
        min-height: 0 !important;
        max-height: calc(100vh - 32px) !important;
        flex: 1 1 auto !important;
        flex-direction: column !important;
        margin: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
    }

    .ccsp-reference-toolbar {
        position: relative !important;
        z-index: 5 !important;
        display: grid !important;
        grid-template-columns: auto minmax(0, 1fr) max-content !important;
        min-height: 58px !important;
        flex: 0 0 auto !important;
        background: #ffffff !important;
    }

    .ccsp-reference-actions {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        white-space: nowrap !important;
    }

    .ccsp-form-cancel,
    .ccsp-form-save {
        display: inline-flex !important;
        min-width: 62px !important;
        min-height: 34px !important;
        flex: 0 0 auto !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .ccsp-form-save {
        border: 1px solid #241819 !important;
        color: #ffffff !important;
        background: #241819 !important;
    }

    .ccsp-page-tabbar {
        flex: 0 0 auto !important;
        background: #ffffff !important;
    }

    .ccsp-reference-body {
        width: min(100%, 650px) !important;
        min-height: 0 !important;
        flex: 1 1 auto !important;
        margin: 0 auto !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        color: #172033 !important;
        background: #ffffff !important;
    }

    .ccsp-reference-row,
    .ccsp-reference-control,
    .ccsp-reference-input-group {
        min-width: 0 !important;
    }

    body.ccsp-modal-open {
        overflow: hidden !important;
    }

    @media (max-width: 680px) {
        .ccsp-modal {
            padding: 0 !important;
        }

        .ccsp-dialog {
            width: 100vw !important;
            max-width: none !important;
            max-height: 100vh !important;
            min-height: 100vh !important;
            border: 0 !important;
            border-radius: 0 !important;
        }

        .ccsp-dialog > form {
            max-height: 100vh !important;
        }

        .ccsp-reference-toolbar {
            grid-template-columns: auto minmax(0, 1fr) !important;
            padding: 8px 10px !important;
        }

        .ccsp-reference-actions {
            grid-column: 1 / -1 !important;
        }
    }

</style>
