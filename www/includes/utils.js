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

function encodeUrl(allURLs)
{
	allURLs = encodeURIComponent(allURLs).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').  replace(/\)/g, '%29').replace(/\*/g, '%2A'); 
	allURLs = allURLs.replace(/%0A/g, '\n')
	return allURLs;
}

function submitenter(myfield,e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13)
	{
		myfield.form.submit();
		return false;
	}
	else
		return true;
}

function headersubmitenter(myfield,e)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;

	if (keycode == 13)
	{
		headersearch();
		return false;
	}
	else
		return true;
}

function headersearch()
{
	var v = document.getElementById("headsearch");
	var vs = document.getElementById("headcat");
	if (v != null && v.value != 'Enter keywords' && vs != null)
	{
		var cat = "";
		if (vs.options[vs.selectedIndex].value != "-1")
			cat = "&t=" + vs.options[vs.selectedIndex].value;
		
		document.location= WWW_TOP + "/search/" + encodeUrl(v.value) + cat;
	}
}

//
// Do ajax call to send nzb url to sab api.
//
function sendToSab(el, host, key, nzb, uid, rsstoken)
{
	var fullsaburl = host + "/api/?mode=addurl&priority=1&apikey=" + key;
	var nzburl = SERVERROOT + "download/sab/nzb/" + nzb + "&i=" + uid + "&r=" + rsstoken;
	alert("SABURL = " + fullsaburl);
	alert("NZBURL = " + nzburl);
}