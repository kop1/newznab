<?
session_start();

if(!is_array($_SESSION['binary'])) {
	$_SESSION['binary'] = array();
}

include('db.php');
include('nzb.class.php');
$nzb = new NZB;

$perpage = 200;

if($_SERVER['REQUEST_METHOD'] == 'POST') {

	if(isset($_REQUEST['getnzb']) && count($_SESSION['binary']) > 0) {
		$str = $nzb->genNZB($_SESSION['binary']);

		//Begin writing headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=yournzb.nzb;");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".strlen($str));
		echo $str;
		exit;
	}


	if(isset($_REQUEST['addnzb']) && count($_REQUEST['binary']) > 0) {
		foreach($_REQUEST['binary'] AS $binary) {
			if(is_array($_SESSION['binary']) && !in_array($binary, $_SESSION['binary'])) {
				$_SESSION['binary'][] = $binary;
			}
		}

	}

	if(isset($_REQUEST['clearnzb'])) {
		$_SESSION['binary'] = array();
	}

}

$res = mysql_query("SELECT COUNT(ID) FROM binaries");
$totbin = mysql_result($res,0,0);
$title =  "NZB Generator; Search within {$totbin} binaries !";


?>
<html>
<head>
<title><?=$title?></title>
<style>
.content {
	font-family: Courier;
	font-size: 9pt;
}
.contentadded {
	font-family: Courier;
	font-size: 9pt;
	color: grey;
}
.head {
	font-family: Courier;
	font-size: 11pt;
	font-weight: bolder;
}
</style>
<script language="javascript">
function SelectAllChkboxes() {
	chk = document.getElementsByTagName('input');
	for(i=0;i<chk.length;i++) {
		if(chk[i].name.indexOf('binary')>-1) {
			chk[i].checked = true;
		}
	}
}
function SelectNoneChkboxes() {
	chk = document.getElementsByTagName('input');
	for(i=0;i<chk.length;i++) {
		if(chk[i].name.indexOf('binary')>-1) {
			chk[i].checked = false;
		}
	}
}
function SelectTotChkboxes() {
	chk = document.getElementsByTagName('input');
	done = false;
	for(i=0;i<chk.length;i++) {
		if(chk[i].name.indexOf('binary')>-1) {
			if(chk[i].checked == false && done == false) {
				chk[i].checked = true;
			} else {
				done = true;
			}
		}
	}
}
function SelectVanafChkboxes() {
	start = false;
	chk = document.getElementsByTagName('input');
	for(i=0;i<chk.length;i++) {
		if(chk[i].name.indexOf('binary')>-1) {
			if(start == false && chk[i].checked == true) {
				start = true;
			}
			if(start == true) {
				chk[i].checked = true;
			}
		}
	}
}
</script>




<script language="javascript">
<!--

function init() {
	document.onkeydown = register;
	document.onkeyup = register;
	document.onclick = register;
}

function register(e)
{
	if (!e) e = window.event;
	var skey = 'shiftKey';
	var ckey = 'crtlKey';
	shiftpressed = e[skey];
	controlpressed = e[ckey];
}

function doThing(e,v) {

	if (!e) e = window.event;
	var skey = 'shiftKey';
	var ckey = 'ctrlKey';
	shiftpressed = e[skey];
	controlpressed = e[ckey];

	if(shiftpressed == false) {
		firstselected = v;
		if(controlpressed == true) {
		} else {
			chk = document.getElementsByTagName('input');
			for(i=0;i<chk.length;i++) {
				if(chk[i].name.indexOf('binary')>-1) {
					if(chk[i].id != v) {
						chk[i].checked = false;
					}
				}
			}
		}
	} else {
		lastselected = v;

		start = false;
		chk = document.getElementsByTagName('input');
		for(i=0;i<chk.length;i++) {
			if(chk[i].name.indexOf('binary')>-1) {
				if(start == false && chk[i].id == firstselected) {
					start = true;
				}
				if(start == true) {
					chk[i].checked = true;
				}
				if(chk[i].id == lastselected)
					break;
			}
		}
	}
}

</SCRIPT>

</head>
<body bgcolor="White" onload="init();">
<?

if($_REQUEST['group'] != '') {
	$gr = " AND groupID = {$_REQUEST['group']} ";
}

if($_REQUEST['clearsearch']) {
	unset($_REQUEST['search']);
}

if($_REQUEST['search'] != '') {
	$search = " AND MATCH (name) AGAINST ('".stripslashes($_REQUEST['search'])."' IN BOOLEAN MODE) ";
}

if($_REQUEST['type'] != '') {
	if($_REQUEST['type'] == 'audio') {
		$type = " AND (name LIKE('%.mp3%') OR name LIKE('%.wma%'))";
	}
	if($_REQUEST['type'] == 'video') {
		$type = " AND (name LIKE('%.avi%') OR name LIKE('%.mpg%') OR name LIKE('%.mpeg%'))";
	}
	if($_REQUEST['type'] == 'images') {
		$type = " AND (name LIKE('%.gif%') OR name LIKE('%.jpg%') OR name LIKE('%.png%') OR name LIKE('%.jpeg%'))";
	}
}

if($_REQUEST['retention'] != '') {
	$retsql = " AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600 / 24 <= {$_REQUEST['retention']}";
	$selretention = $_REQUEST['retention'];
}

if($_REQUEST['offset']) {
	$offset = $_REQUEST['offset'];
} else {
	$offset = 0;
}


echo '<form action='.$_SERVER['PHP_SELF'].' method=GET><select name=group><option value="">all groups</option>';
$res = mysql_query("SELECT * FROM groups WHERE active = 1 ORDER BY name");
while($arr = mysql_fetch_assoc($res)) {
	echo "<option value={$arr['ID']}".($_REQUEST['group']==$arr['ID']?' selected':'').">{$arr['name']} ({$arr['postcount']})</option>";
}
$retention = '<option value="">all</option>';
for($i = 1; $i <= 40; $i++) {
	$retention .= '<option value="'.$i.'"'.($selretention==$i?' selected':'').'>'.$i.'d</option>';
}

echo '</select><input type=text name=search size=40 value="'.htmlspecialchars(stripslashes($_REQUEST['search'])).'"><select name="type"><option value="">all</option><option'.($_REQUEST['type']=='audio'?' selected':'').'>audio</option><option'.($_REQUEST['type']=='video'?' selected':'').'>video</option><option'.($_REQUEST['type']=='images'?' selected':'').'>images</option></select><select name="retention">'.$retention.'</select><input type=submit value=search><input type=submit value=clear name=clearsearch></form>';
$res = mysql_query("SELECT COUNT(ID) FROM binaries WHERE 1 $gr $search $type $retsql");
$tot = mysql_result($res,0,0);
$start = $offset / $perpage == 0 ? 0 : ( ($offset / $perpage) - 10 < 0 ? 0 : ($offset / $perpage) - 10);
$end = $start + 20 > $tot / $perpage ? $tot / $perpage : $start + 20;
$pageskipper = '';
for($i = $start; $i < $end; $i++) {
	$nr = $offset / $perpage == $i ? '<b>'.($i+1).'</b>' : $i+1;
	$pageskipper .= '<a class=content href="'.$_SERVER['PHP_SELF'].'?offset='.($perpage*($i)).'&group='.$_REQUEST['group'].'&type='.$_REQUEST['type'].'&retention='.$_REQUEST['retention'].'&search='.urlencode($_REQUEST['search']).'">'.$nr."</a>&nbsp; ";
}
if($start > 0) {
	$pageskipper = '<a class=content href="'.$_SERVER['PHP_SELF'].'?offset=0&group='.$_REQUEST['group'].'&type='.$_REQUEST['type'].'&retention='.$_REQUEST['retention'].'&search='.$_REQUEST['search'].'">1...</a>&nbsp;'.$pageskipper;
}
if($i <= floor($tot/$perpage)) {
	$pageskipper .= '<a class=content href="'.$_SERVER['PHP_SELF'].'?offset='.(floor($tot/$perpage)*$perpage).'&group='.$_REQUEST['group'].'&type='.$_REQUEST['type'].'&retention='.$_REQUEST['retention'].'&search='.urlencode($_REQUEST['search']).'">...'.(floor($tot/$perpage)+1)."</a>";
}
$pageskipper = '<span class=content>Page:&nbsp;</span>'.$pageskipper;
echo $pageskipper;

$buttons = "<input type=\"submit\" value=\"Add to NZB list\" name=\"addnzb\"><input type=\"submit\" value=\"Clear NZB list\" name=\"clearnzb\"><input type=\"submit\" value=\"Get NZB\" name=\"getnzb\"><span class=content>&nbsp;Currenty ".count($_SESSION['binary'])." in list.</span>";

echo "<br><br><form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">\n";
echo "{$buttons}\n";
echo "<input type=\"hidden\" name=\"offset\" value=\"{$offset}\">\n";
echo "<input type=\"hidden\" name=\"type\" value=\"{$_REQUEST['type']}\">\n";
echo "<input type=\"hidden\" name=\"group\" value=\"{$_REQUEST['group']}\">\n";
echo "<input type=\"hidden\" name=\"search\" value=\"".htmlspecialchars(stripslashes($_REQUEST['search']))."\">\n";
echo "<input type=\"hidden\" name=\"retention\" value=\"{$_REQUEST['retention']}\">\n";
echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\n";
echo "<tr bgcolor=\"#eeeeee\" class=\"head\"><td>&nbsp;</td><td>&nbsp;</td><td>Subject&nbsp;&nbsp;</td><td>Age&nbsp;&nbsp;</td><td>Complete&nbsp;&nbsp;</td><td>Size</td></tr>\n";

$sql = "SELECT
			ID,
			name,
			@tmp:=(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(date)) / 3600,
			IF(@tmp<24,CONCAT(ROUND(@tmp,1),'h'),CONCAT(ROUND(@tmp/24,1),'d')) AS age,
			totalParts
			$search2
		FROM
			binaries
		WHERE 1
		$gr
		$search
		$type
		$retsql
		ORDER BY
			name
		LIMIT {$perpage} OFFSET {$offset}";
$res = mysql_query($sql);

while($arr = mysql_fetch_assoc($res)) {
	$res2 = mysql_query("SELECT COUNT(ID) FROM parts WHERE binaryID = {$arr['ID']}");
	$parts = mysql_result($res2,0,0);
	$res2 = mysql_query("SELECT SUM(size) FROM parts WHERE binaryID = {$arr['ID']}");
	$size = mysql_result($res2,0,0);
	if($arr['totalParts'] > 0) {
		$complete = round($parts / $arr['totalParts'] * 100, 1);
	} else {
		$complete = 0;
	}
	$number = ++$x + $offset;
	if($number%2 == 0) {
		$col = '#eeeeee';
	}  else {
		$col = '#ffffff';
	}
	if(is_array($_SESSION['binary']) && in_array($arr['ID'], $_SESSION['binary'])) {
		$added = 'added';
	} else {
		$added = '';
	}
	echo "<tr bgcolor=\"{$col}\" class=\"content{$added}\"><td>{$number}</td><td>".($added==''?"<input type=\"checkbox\" id=\"checkbox{$arr['ID']}\" name=\"binary[]\" value=\"{$arr['ID']}\" onClick=\"doThing(event,'checkbox{$arr['ID']}');\">":"&nbsp;")."</td><td>{$arr['name']}&nbsp;&nbsp;&nbsp;</td><td align=right>{$arr['age']}&nbsp;&nbsp;&nbsp;</td><td align=right>{$complete}%&nbsp;&nbsp;&nbsp;</td><td align=right>".$nzb->readablesize($size)."</td></tr>\n";
}
echo "</table>\n";
echo "{$buttons}\n";
echo "</form>\n";
echo $pageskipper;

//print_r($_SESSION);

?>
</body>
</html>