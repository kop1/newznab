<?php

//
// general util functions
//
function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();
    
    // if input is object, convert into array
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
    
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}

function makeStringLinksHtml($str, $dereferrer='') {
	return preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;]+)/is', '<a href="'.$dereferrer.'$1://$2" target="_blank">$1://$2</a>', $str);
}

function safeFilename($filename) 
{
    $temp = $filename;
 
    // Lower case
    $temp = strtolower($temp);
 
    // Replace spaces with a '_'
    $temp = str_replace(" ", "_", $temp);
 
    // Loop through string
    $result = '';
    for ($i=0; $i<strlen($temp); $i++) {
        if (preg_match('([0-9]|[a-z]|_)', $temp[$i])) {
            $result = $result . $temp[$i];
        }    
    }
 
    // Return filename
    return $result;
}


function cp437toUTF($str) {
	$out = '';
    for ($i = 0; $i<strlen($str);$i++){	
		$ch = ord($str{$i});
		//echo $ch.' ';
		switch($ch){
			case 128: $out .= 'Ç';break;
			case 129: $out .= 'ü';break;
			case 130: $out .= 'é';break;
			case 131: $out .= 'â';break;
			case 132: $out .= 'ä';break;
			case 133: $out .= 'à';break;
			case 134: $out .= 'å';break;
			case 135: $out .= 'ç';break;
			case 136: $out .= 'ê';break;
			case 137: $out .= 'ë';break;
			case 138: $out .= 'è';break;
			case 139: $out .= 'ï';break;
			case 140: $out .= 'î';break;
			case 141: $out .= 'ì';break;
			case 142: $out .= 'Ä';break;
			case 143: $out .= 'Å';break;
			case 144: $out .= 'É';break;
			case 145: $out .= 'æ';break;
			case 146: $out .= 'Æ';break;
			case 147: $out .= 'ô';break;
			case 148: $out .= 'ö';break;
			case 149: $out .= 'ò';break;
			case 150: $out .= 'û';break;
			case 151: $out .= 'ù';break;
			case 152: $out .= 'ÿ';break;
			case 153: $out .= 'Ö';break;
			case 154: $out .= 'Ü';break;
			case 155: $out .= '¢';break;
			case 156: $out .= '£';break;
			case 157: $out .= '¥';break;
			case 158: $out .= '₧';break;
			case 159: $out .= 'ƒ';break;
			case 160: $out .= 'á';break;
			case 161: $out .= 'í';break;
			case 162: $out .= 'ó';break;
			case 163: $out .= 'ú';break;
			case 164: $out .= 'ñ';break;
			case 165: $out .= 'Ñ';break;
			case 166: $out .= 'ª';break;
			case 167: $out .= 'º';break;
			case 168: $out .= '¿';break;
			case 169: $out .= '⌐';break;
			case 170: $out .= '¬';break;
			case 171: $out .= '½';break;
			case 172: $out .= '¼';break;
			case 173: $out .= '¡';break;
			case 174: $out .= '«';break;
			case 175: $out .= '»';break;
			case 176: $out .= '░';break;
			case 177: $out .= '▒';break;
			case 178: $out .= '▓';break;
			case 179: $out .= '│';break;
			case 180: $out .= '┤';break;
			case 181: $out .= '╡';break;
			case 182: $out .= '╢';break;
			case 183: $out .= '╖';break;
			case 184: $out .= '╕';break;
			case 185: $out .= '╣';break;
			case 186: $out .= '║';break;
			case 187: $out .= '╗';break;
			case 188: $out .= '╝';break;
			case 189: $out .= '╜';break;
			case 190: $out .= '╛';break;
			case 191: $out .= '┐';break;
			case 192: $out .= '└';break;
			case 193: $out .= '┴';break;
			case 194: $out .= '┬';break;
			case 195: $out .= '├';break;
			case 196: $out .= '─';break;
			case 197: $out .= '┼';break;
			case 198: $out .= '╞';break;
			case 199: $out .= '╟';break;
			case 200: $out .= '╚';break;
			case 201: $out .= '╔';break;
			case 202: $out .= '╩';break;
			case 203: $out .= '╦';break;
			case 204: $out .= '╠';break;
			case 205: $out .= '═';break;
			case 206: $out .= '╬';break;
			case 207: $out .= '╧';break;
			case 208: $out .= '╨';break;
			case 209: $out .= '╤';break;
			case 210: $out .= '╥';break;
			case 211: $out .= '╙';break;
			case 212: $out .= '╘';break;
			case 213: $out .= '╒';break;
			case 214: $out .= '╓';break;
			case 215: $out .= '╫';break;
			case 216: $out .= '╪';break;
			case 217: $out .= '┘';break;
			case 218: $out .= '┌';break;
			case 219: $out .= '█';break;
			case 220: $out .= '▄';break;
			case 221: $out .= '▌';break;
			case 222: $out .= '▐';break;
			case 223: $out .= '▀';break;
			case 224: $out .= 'α';break;
			case 225: $out .= 'ß';break;
			case 226: $out .= 'Γ';break;
			case 227: $out .= 'π';break;
			case 228: $out .= 'Σ';break;
			case 229: $out .= 'σ';break;
			case 230: $out .= 'µ';break;
			case 231: $out .= 'τ';break;
			case 232: $out .= 'Φ';break;
			case 233: $out .= 'Θ';break;
			case 234: $out .= 'Ω';break;
			case 235: $out .= 'δ';break;
			case 236: $out .= '∞';break;
			case 237: $out .= 'φ';break;
			case 238: $out .= 'ε';break;
			case 239: $out .= '∩';break;
			case 240: $out .= '≡';break;
			case 241: $out .= '±';break;
			case 242: $out .= '≥';break;
			case 243: $out .= '≤';break;
			case 244: $out .= '⌠';break;
			case 245: $out .= '⌡';break;
			case 246: $out .= '÷';break;
			case 247: $out .= '≈';break;
			case 248: $out .= '°';break;
			case 249: $out .= '∙';break;
			case 250: $out .= '·';break;
			case 251: $out .= '√';break;
			case 252: $out .= 'ⁿ';break;
			case 253: $out .= '²';break;
			case 254: $out .= '■';break;
			case 255: $out .= ' ';break;
			default : $out .= chr($ch);
		}
	}
	return $out;    
}
	
?>