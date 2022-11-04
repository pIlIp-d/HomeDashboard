function string_2_base64(string){
	return btoa(string);
}

function base64_2_string(string){
	return atob(string);
}

function specialchars_2_base64(string){
// ersetzt die folgenden Sonderzeichen durch eine Base64-Kodierung in eckige Klammen gehüllt
// " , & , + , #
	string = string + "";//force string-type
	string = string.replace(/\"/g, btoa("[\"]"));
	string = string.replace(/&/g, btoa("[&]"));
	string = string.replace(/\+/g, btoa("[+]"));
	string = string.replace(/#/g, btoa("[#]"));
	return string;
}

function base64_2_specialchars(string){
// ersetzt die folgenden in eckige Klammen gehüllten kodierten Sonderzeichen durch den eigentlichen Wert
// "(WyJd) , &(WyZd) , +(Wytd) , #(WyNd)
	string = string + "";//force string-type
	string = string.replace(/WyJd/g, "\"");
	string = string.replace(/WyZd/g, "&");
	string = string.replace(/Wytd/g, "+");
	string = string.replace(/WyNd/g, "#");
	return string;
}
