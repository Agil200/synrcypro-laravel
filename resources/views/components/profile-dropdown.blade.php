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

document
.getElementById('notificationTrigger')
?.addEventListener(
'click',
function(){


const box =
document.getElementById(
'notificationDropdown'
);


box.hidden =
!box.hidden;


});



async function loadNotifications(){


try {


const response =
await fetch('/notifications');


const data =
await response.json();



document
.getElementById(
'notificationCount'
)
.innerHTML =
data.length;



let html='';



if(data.length===0){


html=`
<div style="padding:15px">
Tidak ada notifikasi
</div>
`;


}else{


data.forEach(item=>{


html += `

<div class="notification-item">

<strong>
${item.title}
</strong>

<br>

${item.message}

</div>

`;


});


}



document
.getElementById(
'notificationList'
)
.innerHTML = html;



}catch(error){

console.log(error);

}


}


loadNotifications();


setInterval(
loadNotifications,
60000
);


</script>