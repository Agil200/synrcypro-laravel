{{-- resources/views/components/module-shortcut.blade.php --}}

@php
    $shortcutId = 'moduleShortcut';
@endphp

<div class="module-shortcut">
    <button
        type="button"
        class="module-shortcut-trigger"
        id="{{ $shortcutId }}Trigger"
        aria-label="Buka menu utama"
        aria-expanded="false"
        aria-controls="{{ $shortcutId }}Popup"
    >
        <span class="module-shortcut-grid-icon" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>

    <div
        class="module-shortcut-backdrop"
        id="{{ $shortcutId }}Backdrop"
        aria-hidden="true"
    ></div>

    <section
        class="module-shortcut-popup"
        id="{{ $shortcutId }}Popup"
        aria-hidden="true"
        aria-label="Menu utama aplikasi"
    >
        <div class="module-shortcut-panel">
            <div class="module-shortcut-header">
                <div>
                    <strong>MENU UTAMA</strong>
                    <small>Pilih modul yang ingin dibuka</small>
                </div>

                <button
                    type="button"
                    class="module-shortcut-close"
                    id="{{ $shortcutId }}Close"
                    aria-label="Tutup menu utama"
                >
                    &times;
                </button>
            </div>

            <div class="module-shortcut-grid">
                <a
                    href="{{ route('manpower') }}"
                    class="module-shortcut-card {{ request()->routeIs('manpower') ? 'active' : '' }}"
                >
                    <img
                        src="{{ asset('assets/images/LOGO MANPOWER.png') }}"
                        alt=""
                    >
                    <span>MANPOWER</span>
                </a>

                <a
                    href="{{ route('people-development') }}"
                    class="module-shortcut-card {{ request()->routeIs('people-development') ? 'active' : '' }}"
                >
                    <img
                        src="{{ asset('assets/images/LOGO PEOPLE DEVELOPMENT.png') }}"
                        alt=""
                    >
                    <span>PEOPLE DEVELOPMENT</span>
                </a>

                <a
                    href="{{ route('database') }}"
                    class="module-shortcut-card {{ request()->routeIs('database') ? 'active' : '' }}"
                >
                    <img
                        src="{{ asset('assets/images/DATABASE.png') }}"
                        alt=""
                    >
                    <span>DATABASE</span>
                </a>

                <a
                    href="{{ route('admin-all') }}"
                    class="module-shortcut-card {{ request()->routeIs('admin-all') ? 'active' : '' }}"
                >
                    <img
                        src="{{ asset('assets/images/LOGO ADMIN ALL.png') }}"
                        alt=""
                    >
                    <span>ADMIN ALL</span>
                </a>
            </div>
        </div>
    </section>
</div>

@once
    @push('styles')
        <style>
            .module-shortcut {
                display: flex;
                align-items: center;
            }

            .module-shortcut-trigger {
                display: inline-grid;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                place-items: center;
                padding: 0;
                border: 2px solid #111;
                border-radius: 10px;
                color: #111;
                background: #fff;
                cursor: pointer;
                transition:
                    transform .18s ease,
                    box-shadow .18s ease,
                    border-color .18s ease;
            }

            .module-shortcut-trigger:hover {
                border-color: #333;
                box-shadow: 0 7px 16px rgba(0, 0, 0, .14);
                transform: translateY(-2px);
            }

            .module-shortcut-grid-icon {
                display: grid;
                width: 24px;
                height: 24px;
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(2, 1fr);
                gap: 3px;
            }

            .module-shortcut-grid-icon span {
                border: 2px solid currentColor;
                border-radius: 2px;
            }

            .module-shortcut-backdrop {
                position: fixed;
                inset: 64px 0 0;
                z-index: 998;
                visibility: hidden;
                opacity: 0;
                background: rgba(0, 0, 0, .45);
                pointer-events: none;
                transition:
                    opacity .25s ease,
                    visibility .25s ease;
            }

            .module-shortcut-backdrop.is-open {
                visibility: visible;
                opacity: 1;
                pointer-events: auto;
            }

            .module-shortcut-popup {
                position: fixed;
                top: 64px;
                right: 0;
                left: 0;
                z-index: 999;
                visibility: hidden;
                opacity: 0;
                pointer-events: none;
                transform: translateY(-110%);
                transition:
                    transform .42s cubic-bezier(.77, 0, .18, 1),
                    opacity .22s ease,
                    visibility .42s ease;
            }

            .module-shortcut-popup.is-open {
                visibility: visible;
                opacity: 1;
                pointer-events: auto;
                transform: translateY(0);
            }

            .module-shortcut-panel {
                width: 100%;
                padding: 24px 30px 28px;
                border-bottom: 1px solid #d7d7d7;
                background: #fff;
                box-shadow: 0 18px 40px rgba(0, 0, 0, .24);
            }

            .module-shortcut-header {
                display: flex;
                width: min(1050px, 100%);
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                margin: 0 auto 20px;
                padding-bottom: 14px;
                border-bottom: 1px solid #ddd;
            }

            .module-shortcut-header strong {
                display: block;
                color: #111;
                font-size: 18px;
                font-weight: 900;
                letter-spacing: .3px;
            }

            .module-shortcut-header small {
                display: block;
                margin-top: 4px;
                color: #777;
                font-size: 11px;
            }

            .module-shortcut-close {
                display: grid;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                place-items: center;
                padding: 0 0 2px;
                border: 0;
                border-radius: 50%;
                color: #fff;
                background: #c71922;
                box-shadow: 0 5px 12px rgba(199, 25, 34, .28);
                cursor: pointer;
                font-size: 27px;
                line-height: 1;
                transition:
                    transform .18s ease,
                    background .18s ease;
            }

            .module-shortcut-close:hover {
                background: #9f1017;
                transform: rotate(90deg);
            }

            .module-shortcut-grid {
                display: grid;
                width: min(1050px, 100%);
                grid-template-columns: repeat(4, minmax(150px, 1fr));
                gap: 22px;
                margin: 0 auto;
            }

            .module-shortcut-card {
                display: flex;
                min-height: 160px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                padding: 20px;
                overflow: hidden;
                border: 2px solid transparent;
                border-radius: 13px;
                color: #fff;
                background: #0d0b0b;
                box-shadow: 0 9px 22px rgba(0, 0, 0, .16);
                text-align: center;
                text-decoration: none;
                transition:
                    transform .2s ease,
                    border-color .2s ease,
                    box-shadow .2s ease;
            }

            .module-shortcut-card:hover {
                border-color: #e06426;
                box-shadow: 0 13px 28px rgba(0, 0, 0, .24);
                transform: translateY(-4px);
            }

            .module-shortcut-card.active {
                border-color: #e06426;
            }

            .module-shortcut-card img {
                display: block;
                width: 72px;
                height: 72px;
                object-fit: contain;
            }

            .module-shortcut-card span {
                max-width: 100%;
                font-size: 14px;
                font-weight: 900;
                line-height: 1.2;
            }

            @media (max-width: 900px) {
                .module-shortcut-grid {
                    grid-template-columns: repeat(2, minmax(130px, 1fr));
                }
            }

            @media (max-width: 560px) {
                .module-shortcut-panel {
                    padding: 18px 14px 22px;
                }

                .module-shortcut-grid {
                    gap: 12px;
                }

                .module-shortcut-card {
                    min-height: 125px;
                    padding: 14px 8px;
                }

                .module-shortcut-card img {
                    width: 52px;
                    height: 52px;
                }

                .module-shortcut-card span {
                    font-size: 11px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const trigger =
                    document.getElementById('moduleShortcutTrigger');

                const popup =
                    document.getElementById('moduleShortcutPopup');

                const backdrop =
                    document.getElementById('moduleShortcutBackdrop');

                const closeButton =
                    document.getElementById('moduleShortcutClose');

                function setShortcutState(open) {
                    if (!trigger || !popup || !backdrop) {
                        return;
                    }

                    popup.classList.toggle('is-open', open);
                    backdrop.classList.toggle('is-open', open);

                    trigger.setAttribute(
                        'aria-expanded',
                        String(open)
                    );

                    popup.setAttribute(
                        'aria-hidden',
                        String(!open)
                    );

                    backdrop.setAttribute(
                        'aria-hidden',
                        String(!open)
                    );
                }

                if (!trigger || !popup || !backdrop) {
                    return;
                }

                trigger.addEventListener('click', function () {
                    const isOpen =
                        popup.classList.contains('is-open');

                    setShortcutState(!isOpen);
                });

                backdrop.addEventListener('click', function () {
                    setShortcutState(false);
                    trigger.focus();
                });

                if (closeButton) {
                    closeButton.addEventListener('click', function () {
                        setShortcutState(false);
                        trigger.focus();
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (
                        event.key === 'Escape' &&
                        popup.classList.contains('is-open')
                    ) {
                        setShortcutState(false);
                        trigger.focus();
                    }
                });
            });
        </script>
    @endpush
@endonce