<div class="sp-notification">

    <button 
        class="sp-notification-btn"
        onclick="toggleNotification()">

        🔔

        <span 
        id="notificationBadge"
        class="sp-notification-badge">
            0
        </span>

    </button>


    <div 
    id="notificationPanel"
    class="sp-notification-panel">


        <div class="sp-notification-header">
            NOTIFIKASI
        </div>


        <div 
        id="notificationContent">

            Memuat notifikasi...

        </div>


    </div>

</div>


<script>

function toggleNotification(){

    let panel =
    document.getElementById(
        'notificationPanel'
    );


    panel.style.display =
    panel.style.display === 'block'
    ?
    'none'
    :
    'block';

}



async function loadNotifications(){


let response =
await fetch(
    "{{ route('notifications') }}"
);


let data =
await response.json();



document
.getElementById(
'notificationBadge'
)
.innerHTML=data.length;



let html='';


if(data.length===0){

html=`
<div class="empty-notif">
Tidak ada notifikasi
</div>
`;

}
else{


data.forEach(item=>{


html+=`

<div class="notif-item">

<div class="notif-title">
${item.title}
</div>


<div class="notif-message">
${item.message}
</div>


</div>

`;


});


}


document
.getElementById(
'notificationContent'
)
.innerHTML=html;


}


loadNotifications();


setInterval(
loadNotifications,
60000
);


</script>