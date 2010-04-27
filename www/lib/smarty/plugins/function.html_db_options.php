<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_db_options} function plugin
 *
 * Type:     function<br>
 * Name:     html_db_options<br>
 * Input:<br>
 *           - name       (optional) - string default "select"
 *           - data       (required) - 2D array from database
 *           - valuefield (required) - value to use as option ID
 *           - textfield  (optional) - value to use as label - if missing valuefield field is used
 *           - selected   (optional) - string default not set
  * Purpose:  Prints a <select> list generated from
 *           the passed parameters
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_db_options($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    
    $name = null;
    $valuefield = null;
    $textfield = null;
    $selected = array();
    $data = array();
    
    $extra = '';
    
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
			case 'valuefield':
            case 'textfield':
                $$_key = (string)$_val;
                break;
                
			case 'selected':
			    $$_key = array_map('strval', array_values((array)$_val));
                break;
				
			case 'data':	
				
                $$_key = (array)$_val;
                break;
				
            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_options: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (!isset($data) || !isset($valuefield) )
        return ''; /* raise error here? */
        
   
			
	if (!isset($textfield))
	{		
		$textfield = $valuefield;
	}
		
    $_html_result = '';

foreach ($data as $row )
	{
		$_html_result .= '<option label="' . smarty_function_escape_special_chars($row[$textfield]) . '" value="' .
            smarty_function_escape_special_chars($row[$valuefield]) . '"';
        if (in_array((string)$row[$valuefield], $selected))
            $_html_result .= ' selected="selected"';
        $_html_result .= '>' . smarty_function_escape_special_chars($row[$textfield]) . '</option>' . "\n";
	}
	
    if(!empty($name)) {
        $_html_result = '<select name="' . $name . '"' . $extra . '>' . "\n" . $_html_result . '</select>' . "\n";
    }

    return $_html_result;
}


?>
