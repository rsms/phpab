window.onload = function(){
	var p = document.location.href.indexOf('?');
	if(p != -1) document.getElementById('mainframe').setAttribute("src", document.location.href.substring(p+1));
}