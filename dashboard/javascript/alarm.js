
var ALARM_INTERVALL_MAIN = setInterval(alarm_interval, 1000);

class Alarm(){

    constructor(){

    }
    


}


function alarm_interval(){

}




window.addEventListener('message', e => {//TODO session id for moving
  	if (e.origin == "http://"+location.host){
        let data_str = e.data.split(" ");
		switch (data_str[1]){
            case "set_alarm":

        }

	}
}
