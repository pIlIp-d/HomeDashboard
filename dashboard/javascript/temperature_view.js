function change_view(input = 0){
	let show = document.getElementById("show");
	if (input == 1)
		show.innerHTML = show.innerHTML ^ 1;
	if (show.innerHTML == 0){
		const list = ["header_symbol","tmin","tmax","tmin_label","tmax_label"];
		for (let i in list){
			document.getElementById(list[i]).style.visibility = "visible";
	 	}
		document.getElementById("header_text").style.fontSize = "1.5em";
		document.getElementById("tact").style.fontSize = "3em";
	} else {
		
		const list = ["header_symbol","tmin","tmax","tmin_label","tmax_label"];
		for (let i in list){
			document.getElementById(list[i]).style.visibility = "hidden";
	 	}
	 	document.getElementById("header_text").style.fontSize = "180%";
		document.getElementById("tact").style.fontSize = "450%";		
	}
}