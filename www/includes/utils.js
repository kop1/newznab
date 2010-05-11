function gotoUrl(url)
{
	window.location = url;
}

function setFocus(id)
{
	var v = document.getElementById(id);
	if (v != null)
		v.focus();
}