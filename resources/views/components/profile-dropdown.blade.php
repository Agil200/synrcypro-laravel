@php
    $user = auth()->user();

    $profilePhoto = $user?->avatar
        ? $user->avatar
        : asset('assets/images/profile.png');
@endphp

<style>
    .syn-notification-dropdown {
        width: min(390px, calc(100vw - 24px));
        max-height: min(480px, calc(100vh - 90px));
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .20);
    }

    .syn-notification-title {
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 13px 16px;
        color: #ffffff;
        background: #171717;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: .04em;
    }

    #notificationList {
        max-height: 410px;
        overflow-y: auto;
        background: #f8fafc;
        scrollbar-width: thin;
    }

    .syn-notification-empty,
    .syn-notification-error {
        padding: 22px 16px;
        color: #64748b;
        text-align: center;
        font-size: 13px;
    }

    .syn-notification-error {
        color: #991b1b;
        background: #fff1f2;
    }

    .notification-item {
        margin: 10px;
        padding: 13px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .04);
        font-size: 12px;
        line-height: 1.45;
    }

    .notification-item[data-read="false"] {
        border-left: 4px solid #ea580c;
        cursor: pointer;
    }

    .notification-item[data-read="true"] {
        opacity: .82;
    }

    .syn-notification-item-title {
        display: block;
        margin-bottom: 5px;
        color: #172033;
        font-size: 13px;
        font-weight: 900;
    }

    .syn-notification-message,
    .syn-birthday-summary {
        margin: 0;
        color: #64748b;
    }

    .syn-birthday-list {
        display: grid;
        gap: 7px;
        margin: 10px 0 0;
        padding: 0;
        list-style: none;
    }

    .syn-birthday-person {
        position: relative;
        padding: 8px 9px 8px 30px;
        color: #334155;
        border-radius: 8px;
        background: #fff7ed;
        overflow-wrap: anywhere;
    }

    .syn-birthday-person::before {
        position: absolute;
        top: 8px;
        left: 9px;
        content: '🎉';
        font-size: 12px;
    }

    @media (max-width: 520px) {
        .syn-notification-dropdown {
            position: fixed;
            top: 64px;
            right: 12px;
            left: 12px;
            width: auto;
        }
    }
</style>


{{-- NOTIFICATION --}}
<div class="syn-notification-wrapper">

    <button
        type="button"
        class="syn-notification-trigger"
        id="notificationTrigger"
        aria-label="Notifikasi">


        🔔


        <span 
            class="syn-notification-badge"
            id="notificationCount">
            0
        </span>


    </button>


    <div
        class="syn-notification-dropdown"
        id="notificationDropdown"
        hidden>


        <div class="syn-notification-title">
            NOTIFIKASI
        </div>


        <div id="notificationList">

            Memuat notifikasi...

        </div>


    </div>


</div>




{{-- PROFILE --}}
<div
    class="syn-profile-wrapper"
    id="profileWrapper">


    {{-- Tombol foto profil --}}
    <button
        type="button"
        class="syn-profile-trigger"
        id="profileTrigger"
        aria-label="Buka menu profil"
        aria-expanded="false">


        <img
            src="{{ $profilePhoto }}"
            alt="Foto profil {{ $user?->name ?? 'Pengguna' }}"
            referrerpolicy="no-referrer">


    </button>



    {{-- Dropdown Profil --}}
    <div
        class="syn-profile-dropdown"
        id="profileDropdown"
        hidden>



        <div class="syn-profile-header">


            <img
                src="{{ $profilePhoto }}"
                class="syn-profile-photo"
                alt="Foto profil">


            <div class="syn-profile-information">


                <strong>
                    {{ $user?->name ?? 'Calvin Anggoro' }}
                </strong>


                <span>
                    NRP:
                    {{ $user?->nrp ?? '10001' }}
                </span>


                <span>

                    {{
                        $user?->jabatan
                        ?? $user?->role
                        ?? 'Supervisor Produksi'
                    }}

                </span>


            </div>


        </div>



        <div class="syn-profile-line"></div>



        <div class="syn-profile-menu">


            <a
                href="{{ route('profile.index') }}"
                class="syn-profile-item">

                <span>👤</span>
                <span>Profil Saya</span>

            </a>



            <a
                href="{{ route('profile.settings') }}"
                class="syn-profile-item">

                <span>⚙️</span>
                <span>Pengaturan Akun</span>

            </a>



            <a
                href="{{ route('profile.change-email') }}"
                class="syn-profile-item">

                <span>✉️</span>
                <span>Ubah Email</span>

            </a>



            <form
                method="POST"
                action="{{ route('logout') }}">

                @csrf


                <button
                    type="submit"
                    class="syn-profile-item syn-profile-signout">


                    <span>🚪</span>
                    <span>Keluar</span>


                </button>


            </form>


        </div>


    </div>


</div>



<script>

const notificationUrl = @json(url('/notifications'));
const notificationCountUrl = @json(url('/notifications/count'));
const notificationReadBaseUrl = @json(url('/notifications/read'));

const notificationTrigger =
    document.getElementById('notificationTrigger');

const notificationDropdown =
    document.getElementById('notificationDropdown');

const notificationList =
    document.getElementById('notificationList');

const notificationCount =
    document.getElementById('notificationCount');

const csrfToken =
    document.querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

notificationTrigger?.addEventListener('click', function () {
    if (!notificationDropdown) {
        return;
    }

    notificationDropdown.hidden = !notificationDropdown.hidden;

    if (!notificationDropdown.hidden) {
        loadNotifications();
    }
});

async function fetchJson(url, options = {}) {
    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers || {})
    };

    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers
    });

    const contentType = response.headers.get('content-type') || '';

    if (!contentType.includes('application/json')) {
        throw new Error('Respons notifikasi bukan JSON.');
    }

    const payload = await response.json();

    if (!response.ok || payload.success === false) {
        throw new Error(
            payload.message || 'Notifikasi tidak dapat dimuat.'
        );
    }

    return payload;
}

function updateNotificationBadge(count) {
    if (!notificationCount) {
        return;
    }

    const normalizedCount = Math.max(0, Number(count) || 0);
    notificationCount.textContent = String(normalizedCount);
}

function renderNotifications(items) {
    if (!notificationList) {
        return;
    }

    notificationList.replaceChildren();

    if (!Array.isArray(items) || items.length === 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'syn-notification-empty';
        emptyState.textContent = 'Tidak ada notifikasi';
        notificationList.appendChild(emptyState);
        return;
    }

    items.forEach(function (item) {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item';
        notificationItem.dataset.notificationId = String(item.id || '');
        notificationItem.dataset.read = item.is_read ? 'true' : 'false';

        const title = document.createElement('strong');
        title.className = 'syn-notification-item-title';
        title.textContent = String(item.title || 'Notifikasi');

        notificationItem.appendChild(title);

        if (item.type === 'birthday') {
            appendBirthdayMessage(
                notificationItem,
                String(item.message || '')
            );
        } else {
            const message = document.createElement('p');
            message.className = 'syn-notification-message';
            message.textContent = String(item.message || '');
            notificationItem.appendChild(message);
        }

        if (!item.is_read && item.id) {
            notificationItem.addEventListener('click', function () {
                markNotificationAsRead(item.id, notificationItem);
            }, { once: true });
        }

        notificationList.appendChild(notificationItem);
    });
}

function appendBirthdayMessage(container, rawMessage) {
    const separatorIndex = rawMessage.indexOf(':');
    const summaryText = separatorIndex >= 0
        ? rawMessage.slice(0, separatorIndex).trim()
        : 'Daftar karyawan yang berulang tahun hari ini';
    const peopleText = separatorIndex >= 0
        ? rawMessage.slice(separatorIndex + 1)
        : rawMessage;
    const people = peopleText
        .split(';')
        .map((person) => person.trim())
        .filter(Boolean);

    const summary = document.createElement('p');
    summary.className = 'syn-birthday-summary';
    summary.textContent = summaryText;
    container.appendChild(summary);

    if (people.length === 0) {
        return;
    }

    const list = document.createElement('ul');
    list.className = 'syn-birthday-list';

    people.forEach((person) => {
        const listItem = document.createElement('li');
        listItem.className = 'syn-birthday-person';
        listItem.textContent = person;
        list.appendChild(listItem);
    });

    container.appendChild(list);
}

async function loadNotificationCount() {
    try {
        const payload = await fetchJson(notificationCountUrl);
        updateNotificationBadge(payload.count);
    } catch (error) {
        console.error('Notification count:', error);
    }
}

async function loadNotifications() {
    try {
        const payload = await fetchJson(notificationUrl);
        const items = Array.isArray(payload.data) ? payload.data : [];

        renderNotifications(items);
        updateNotificationBadge(payload.unread_count ?? payload.count);
    } catch (error) {
        console.error('Notifications:', error);

        if (notificationList) {
            notificationList.replaceChildren();
            const errorState = document.createElement('div');
            errorState.className = 'syn-notification-error';
            errorState.textContent =
                'Notifikasi tidak dapat dimuat. Silakan coba lagi.';
            notificationList.appendChild(errorState);
        }
    }
}

async function markNotificationAsRead(id, element) {
    try {
        const payload = await fetchJson(
            `${notificationReadBaseUrl}/${encodeURIComponent(id)}`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            }
        );

        element.dataset.read = 'true';
        updateNotificationBadge(payload.count);
    } catch (error) {
        console.error('Mark notification as read:', error);
    }
}

loadNotifications();
loadNotificationCount();

setInterval(function () {
    loadNotifications();
    loadNotificationCount();
}, 60000);


</script>