<div class="notification-box">

<button id="notificationBtn">

🔔

<span id="notificationCount">
0
</span>

</button>


<div id="notificationMenu"
style="display:none">

<h4>
Notifikasi
</h4>


<div id="notificationList">

Loading...

</div>


</div>


</div>



<script>


async function loadNotification(){


let res =
await fetch('/notifications');


let data =
await res.json();


document
.getElementById(
'notificationCount'
)
.innerHTML=data.length;



document
.getElementById(
'notificationList'
)
.innerHTML=data.map(n=>`

<div class="notif-item">

<b>${n.title}</b>

<br>

${n.message}

</div>

`).join('');

}



document
.getElementById(
'notificationBtn'
)
.onclick=function(){

let x=document
.getElementById(
'notificationMenu'
);


x.style.display =
x.style.display==='none'
?'block'
:'none';

}



loadNotification();


</script>