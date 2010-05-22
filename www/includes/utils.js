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
	var fullsaburl = host + "api/?mode=addurl&priority=1&apikey=" + key;
	var nzburl = SERVERROOT + "download/sab/nzb/" + nzb + "&i=" + uid + "&r=" + rsstoken;
  xmlhttp=GetXmlHttpObject();
  if (xmlhttp==null)
  {
          alert ("Browser does not support HTTP Request");
          return;
  }
	
	var url=fullsaburl + "&rand=" + Math.floor(Math.random()*100000) + "&name=" + escape(nzburl) ;
	xmlhttp.onreadystatechange=function(){ jsStateChanged( 1, el ); };
	xmlhttp.open("POST",url,true);
	xmlhttp.send(null);
}

function jsStateChanged(ty, el)
{
	if (ty == 1)
	{
	  if (xmlhttp.readyState==4)
	  {
	  	el.innerText = "[Sent to Sab]";
	    el.title = "added to queue";
	    el.onclick = "return false;";
	    el.href = "#";
	  }
	}
}


function GetXmlHttpObject()
{
	if (window.XMLHttpRequest)
  {
	  // code for IE7+, Firefox, Chrome, Opera, Safari
  	return new XMLHttpRequest();
  }
	if (window.ActiveXObject)
  {
  	// code for IE6, IE5
	  return new ActiveXObject("Microsoft.XMLHTTP");
  }
	return null;
}

