
const HOMESERVER_URL = "/HomeDashboard";
const DB_URL = HOMESERVER_URL + "/odk_db.php";
const INTERVALL_MAIN_TICKER = 1000;
var INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
var REFRESHED = false;

//---init---
const DASHBOARD = new Dashboard();
DASHBOARD.init();

//executes function(with value as para) when select_preset changes state
document.querySelector('#select_preset').addEventListener("change", function() {
    if (document.getElementById("new").value == this.value){
        DASHBOARD.action(this.value,"new");
        document.getElementById("select_preset").value = "null";
    }
    else if (document.getElementById("null").value == this.value)
        return;
    else {
        last_preset = this.value;
        document.getElementById("select_preset").value = this.value;
    }
});

window.addEventListener('DOMContentLoaded', init());


var window_width = window.innerWidth;
function interval_main_tick(){
    if (window_width <= 600){//checks if orientation has been changed
        if (window.innerWidth > 600){
            window_width = window.innerWidth;
            DASHBOARD.grid_vertical = DASHBOARD.grid;
            DASHBOARD.grid = DASHBOARD.grid_horizontal;
            DASHBOARD.grid.update();
        }
    }
    else if (window_width > 600 && window.innerWidth <= 600){
        window_width = window.innerWidth;
        DASHBOARD.grid_horizontal = DASHBOARD.grid;
        DASHBOARD.grid = DASHBOARD.grid_vertical;
        DASHBOARD.grid.update();
    }
}
function init(){
    //DOMCONTENTLOADED
}

function body_onscroll(){//NOT REALLY USED
    let box = document.getElementById('body').getBoundingClientRect();
    // msg-Objekt erzeugen
    body = document.getElementById("body");
    msg = document.createElement("div");
    msg.classList.add("msg");
    msg.id = "msg";
    msg.innerHTML = "reloading...";
    body.appendChild(msg);
  // Seite neu laden und msg für 1 Sekunde anzeigen
    if ((box.top > 100) && (REFRESHED == false)){
        REFRESHED = true;
        setTimeout (function() {
        location.reload(true);
            // msg-Objekt entfernen
            body.removeChild(msg);
        }, 1000);
        alert("button");
        let new_gridbox = new grid_object(2,1);
        new_gridbox.create(100);
    }
    else{
        REFRESHED = false;
        body.removeChild(msg);
    }
}
document.getElementById('container').addEventListener("click",function() {
    if (document.getElementById('settings-menu').className.includes('open')){
        click_sidebar();
    }
});

var toggle_time = new Date().valueOf();
function click_sidebar(sender){
    if (new Date().valueOf() > toggle_time){
        document.getElementById('settings-menu').classList.toggle("open");
        toggle_time = new Date().valueOf();
    }
}

//-----------------------------------------
//-------- Message Handling ---------------
//-----------------------------------------

	window.addEventListener('message', e => {//TODO session id for moving
	  	if (e.origin == "http://"+location.host){
			let data_str = e.data.split(" ");
			switch (data_str[1]){
				//move with arrows
				case "button_up":
					DASHBOARD.grid.move_up(Number(data_str[0]));
					break;
				case "button_left":
					DASHBOARD.grid.move_left(Number(data_str[0]));
					break;
				case "button_right":
					DASHBOARD.grid.move_right(Number(data_str[0]));
					break;
				case "button_down":
					DASHBOARD.grid.move_down(Number(data_str[0]));
					break;
				case "click_ok":
					click_ok(data_str[0]);
					break;
				case "click_delete":
					click_delete(data_str[0]);
					break;
				case "set_show"://for temp widget view
					DASHBOARD.grid[data_str[0]].display = DASHBOARD.grid[data_str[0]].display ^ 1;
					break;
				case "reload":
					DASHBOARD.grid.update();
					break;
			}
		}
	}, false);
