
function toggle_bp_mode(bool = null){
    if (bool != null)//set
        bp_mode = bool^1;//toogle twice -> set to actual bool value
    changed = true;
    //toggle content
    if (bp_mode) {
        TEXT_AREA.style.display = "block";
        BP_TEXT.style.display = "none";
        NOTE_SELECT.disabled = "";
        document.getElementById("bp_mode").src = HOMESERVER_URL+"images/btn_list_regular.svg";
    }
    else{
        TEXT_AREA.style.display = "none";
        BP_TEXT.style.display = "block";
        NOTE_SELECT.disabled = "true";
        document.getElementById("bp_mode").src = HOMESERVER_URL+"images/btn_list_solid.svg";
    }
    bp_mode = 1^bp_mode;
}
function set_active_recipe_text() {
    xhttp_request("get_active_recipe", true);
}

/**
 * http request sending and -> to response handling
 * @param request_name used on serverside to create response
 * @param async
 * @param value
 */
function xhttp_request(request_name, async = false, value = null){
    let json_string = "{"
    json_string += "\"request_name\":\"" + request_name + "\"";
    switch(request_name) {
        case "get_active_recipe":
            break;
        case "save_note":
            json_string = {"request_name": request_name};
            json_string.note = value;
            json_string = JSON.stringify(json_string);
            json_string = json_string.substring(0, json_string.length-1);//remove } from end
            break;
    }
    json_string += "}";


    // HTTP-Request an Server schicken
    let response = "";
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200){
            response = this.responseText;
            if (async)
                response_handler(request_name, response);
        }
        else if (this.status === 404){
            if (simpleErrorCounter++ < 1)
                alert("No Data for "+ NAME.innerHTML +" Found!\nMaybe the database entry hasn't been made yet.");
            else
                console.log(404 );
        }

    };
    xhttp.open("GET", DB_URL + "?json=" + json_string, async);
    xhttp.send();
    if (!async)
        response_handler(request_name, response);

}

function save_input(){
    /**
     * @var input - raw string of textarea value
     * @var heading - maximum first three words
     */
    let input = TEXT_AREA.value;
    if (input !== "") {
        let first_line = input.trimStart().split("\n")[0].replace("\n");

        let wordCount = first_line.split(" ").length;
        //place first 3 word of first line in heading array;
        let heading = first_line.split(" ").slice(0, Math.min(wordCount, 3));// max 3 words from the first line are the title

        //remove empty words from heading
        cleanLoop:while (true){
            for (h in heading){
                if (heading[h] === ''){
                    heading.remove(h);
                    continue cleanLoop;//restart clean
                }
            }
            break;
        }

        payload = {"heading": heading, "text_value":input};
        if (text_value_id != null)
            payload.id = text_value_id;
        xhttp_request("save_note", true, payload);

    }
}

function response_handler(request_name, response){

    switch(request_name) {
        case "get_active_recipe":
            let json_response = JSON.parse(response);
            act_recipe = json_response[0];
            BP_TEXT.innerHTML = act_recipe.preparation;
            break;
    }
}