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
