@php
    $user = auth()->user();

    $profilePhoto = $user?->avatar
        ? $user->avatar
        : asset('assets/images/profile.png');
@endphp


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
        emptyState.style.padding = '15px';
        emptyState.textContent = 'Tidak ada notifikasi';
        notificationList.appendChild(emptyState);
        return;
    }

    items.forEach(function (item) {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item';
        notificationItem.dataset.notificationId = String(item.id || '');

        const title = document.createElement('strong');
        title.textContent = String(item.title || 'Notifikasi');

        const message = document.createTextNode(
            String(item.message || '')
        );

        notificationItem.appendChild(title);
        notificationItem.appendChild(document.createElement('br'));
        notificationItem.appendChild(message);

        if (!item.is_read && item.id) {
            notificationItem.addEventListener('click', function () {
                markNotificationAsRead(item.id, notificationItem);
            }, { once: true });
        }

        notificationList.appendChild(notificationItem);
    });
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
            notificationList.textContent =
                'Notifikasi tidak dapat dimuat. Silakan coba lagi.';
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