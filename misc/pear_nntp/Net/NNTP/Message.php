<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * 
 * 
 * PHP versions 4 and 5
 *
 * <pre>
 * +-----------------------------------------------------------------------+
 * |                                                                       |
 * | W3C® SOFTWARE NOTICE AND LICENSE                                      |
 * | http://www.w3.org/Consortium/Legal/2002/copyright-software-20021231   |
 * |                                                                       |
 * | This work (and included software, documentation such as READMEs,      |
 * | or other related items) is being provided by the copyright holders    |
 * | under the following license. By obtaining, using and/or copying       |
 * | this work, you (the licensee) agree that you have read, understood,   |
 * | and will comply with the following terms and conditions.              |
 * |                                                                       |
 * | Permission to copy, modify, and distribute this software and its      |
 * | documentation, with or without modification, for any purpose and      |
 * | without fee or royalty is hereby granted, provided that you include   |
 * | the following on ALL copies of the software and documentation or      |
 * | portions thereof, including modifications:                            |
 * |                                                                       |
 * | 1. The full text of this NOTICE in a location viewable to users       |
 * |    of the redistributed or derivative work.                           |
 * |                                                                       |
 * | 2. Any pre-existing intellectual property disclaimers, notices,       |
 * |    or terms and conditions. If none exist, the W3C Software Short     |
 * |    Notice should be included (hypertext is preferred, text is         |
 * |    permitted) within the body of any redistributed or derivative      |
 * |    code.                                                              |
 * |                                                                       |
 * | 3. Notice of any changes or modifications to the files, including     |
 * |    the date changes were made. (We recommend you provide URIs to      |
 * |    the location from which the code is derived.)                      |
 * |                                                                       |
 * | THIS SOFTWARE AND DOCUMENTATION IS PROVIDED "AS IS," AND COPYRIGHT    |
 * | HOLDERS MAKE NO REPRESENTATIONS OR WARRANTIES, EXPRESS OR IMPLIED,    |
 * | INCLUDING BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY OR        |
 * | FITNESS FOR ANY PARTICULAR PURPOSE OR THAT THE USE OF THE SOFTWARE    |
 * | OR DOCUMENTATION WILL NOT INFRINGE ANY THIRD PARTY PATENTS,           |
 * | COPYRIGHTS, TRADEMARKS OR OTHER RIGHTS.                               |
 * |                                                                       |
 * | COPYRIGHT HOLDERS WILL NOT BE LIABLE FOR ANY DIRECT, INDIRECT,        |
 * | SPECIAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF ANY USE OF THE        |
 * | SOFTWARE OR DOCUMENTATION.                                            |
 * |                                                                       |
 * | The name and trademarks of copyright holders may NOT be used in       |
 * | advertising or publicity pertaining to the software without           |
 * | specific, written prior permission. Title to copyright in this        |
 * | software and any associated documentation will at all times           |
 * | remain with copyright holders.                                        |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 * </pre>
 *
 * @category   Net
 * @package    Net_NNTP
 * @author     Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright  2002-2005 Heino H. Gehlsen <heino@gehlsen.dk>. All Rights Reserved.
 * @license    http://www.w3.org/Consortium/Legal/2002/copyright-software-20021231 W3C® SOFTWARE NOTICE AND LICENSE
 * @version    CVS: $Id: Message.php,v 1.12 2006/02/19 00:47:08 heino Exp $
 * @link       http://pear.php.net/package/Net_NNTP
 * @see        
 * @since      File available (in experimental alpha releases) since release 0.10.0
 * @deprecated File deprecated in Release 1.3.0
 */

/**
 *
 */
require_once 'PEAR.php';

/**
 *
 */
require_once 'Net/NNTP/Header.php';


trigger_error('Experimental class Net_NNTP_Message has been deprecated, and will be removed from the Net_NNTP package!', E_USER_WARNING);


// {{{ Net_NNTP_Message

/**
 * The Net_NNTP_Message class
 *
 * @category   Net
 * @package    Net_NNTP
 * @author     Heino H. Gehlsen <heino@gehlsen.dk>
 * @version    package: 1.4.0 (stable)
 * @version    api: 0.8.1 (alpha)
 * @access     public
 * @see        Net_NNTP_Header
 * @since      Class available since Release 0.10.0
 * @ignore
 */
class Net_NNTP_Message
{
    // {{{ properties

    /*
     * Contains the message's header object
     *
     * @var    object
     * @access public
     */
    var $header;
				 
    /**
     * Contains the body part of the message
     *
     * @var    string
     * @access public
     */
    var $body;

    // }}}
    // {{{ constructor

    /**
     * Constructor.
     *
     * @access public
     */
    function Net_NNTP_Message($input = null)
    {
	$this->reset();
	$this->setMessage($input);
    }

    // }}}
    // {{{ reset()

    /**
     * Resets the message object
     *
     * @access public
     */
    function reset()
    {
	$this->header = new Net_NNTP_Header();
	$this->body = null;
    }

    // }}}
    // {{{ create()
    
    /**
     * Create a new instance of Net_NNTP_Message
     *
     * @param optional mixed $input  Can be any of the following:
     *                               (string) RFC2822 message lines (RCLF included)
     *                               (array)  RFC2822 message lines (RCLF not included)
     *                               (object) Net_NNTP_Header object
     *                               (object) Net_NNTP_Message object
     * @param optional mixed $input2 If given, $input will only be use for the message's
     *                               header, while $input2 will be used for the body.
     *                               (Disallowed when $input is a Net_NNTP_Message)
     *
     * @access public
     * @since 0.1
     */
    function & create($input = null, $input2 = null)
    {
	$Object = new Net_NNTP_Message();
	
	switch (true) {

	    // Null
	    case (is_null($input) && is_null($input2)):
	        return $Object;
		break;


	    // Object 
	    case is_object($input):
		switch (true) {
		    
		    // Header
		    case is_a($input, 'net_nntp_header'):
			$Object->setHeader($input);
			$Object->setBody($input2);
			return $Object;
			break;
			
		    // Message
		    case is_a($input, 'net_nntp_message'):
			if ($input2 != null) {
			    return PEAR::throwError('Second parameter not allowed!', null);
			}			
			return $input;
			break;
			
		    // Unknown object/class
		    default:
			return PEAR::throwError('Unsupported object/class: '.get_class($input), null);
		}
		break;

	    // Array & String (only 1st parameter)
	    case ((is_string($input) || is_array($input)) && (is_null($input2))):
		$Object->setMessage($input);
		return $Object;
		break;

	    // Array & String (also 2nd parameter)
	    case ((is_string($input) || is_array($input)) && (is_string($input2) || is_array($input2))):

		$Object->setHeader($input);

		if (is_array($input2)) {
		    $Object->body = implode("\r\n", $input2);
		} else {
		    $Object->body = $input2;
		}

		return $Object;
		break;

	    // Unknown type
	    default:
		return PEAR::throwError('Unsupported object/class: '.get_class($input), null);
	}
    }

    // }}}
    // {{{ setMessage()

    /**
     * Sets the header and body grom the given $message
     *
     * @param mixed $message Can be any of the following:
     *                       (string) RFC2822 message lines (RCLF included)
     *                       (array)  RFC2822 message lines (RCLF not included)
     *                       (object) Net_NNTP_Message object
     *
     * @access public
     */
    function setMessage($message)
    {
	switch (true) {

	    // Object
	    case is_object($message);
	        switch (true) {

		    // Message
		    case is_a($input, 'net_nntp_message'):
				$this->setHeader($message->getHeader());
				$this->setBody($message->getBody());
		        break;

		    // Unknown object/class
		    default:
		        return PEAR::throwError('Unsupported object/class: '.get_class($message), null);
		}
		break;

	    // Array & String
	    case is_array($message):
	    case is_string($message):
		$array = $this->splitMessage($message);
		$this->setHeader($array['header']);
		$this->setBody($array['body']);
	        break;
		
	    // Unknown type
	    default:
	        return PEAR::throwError('Unsupported type: '.gettype($message), null);
	}
    }

    // }}}
    // {{{ getMessageString()

    /**
     * Get the complete transport-ready message as a string
     *
     * @return string
     * @access public
     */
    function getMessageString()
    {
	return $this->header->getFieldsString()."\r\n\r\n".$this->getBody();
    }

    // }}}
    // {{{ getMessageArray()

    /**
     * Get the complete transport-ready message as an array
     *
     * @return string
     * @access public
     */
    function getMessageArray()
    {
	// Get the header fields
	$header = $this->header->getFieldsArray();
	
	// Append null line
	$header[] = '';
	
	// Merge with body, and return
	return array_merge($header, explode("\r\n", $this->getBody()));
    }

    // }}}
    // {{{ setHeader()

    /**
     * Sets the header's fields from the given $input
     *
     * @param mixed $input Can be any of the following:
     *                     (string) RFC2822 message lines (RCLF included)
     *                     (array)  RFC2822 message lines (RCLF not included)
     *                     (object) Net_NNTP_Header object
     *
     * @access public
     */
    function setHeader($input)
    {
	    switch (true) {

		// Object
		case is_object($input):
		    switch (true) {
		    
			// Header
			case is_a($input, 'net_nntp_header'):
		    	    $this->header = $input;
			    break;

			// Unknown object/class
			default:
			    return PEAR::throwError('Unsupported object/class: '. get_class($input), null);
		    }
		    break;

		// Array & String
		case is_array($input):
		case is_string($input):
		    $this->header->setFields($input);
		    break;

		// Unknown type
		default:
		    return PEAR::throwError('Unsupported type: '. gettype($input), null);
	    }
    }

    // }}}
    // {{{ getHeader()

    /**
     * Gets the header object
     *
     * @return object
     * @access public
     */
    function getHeader()
    {
	return $this->header;
    }

    // }}}
    // {{{ setBody()

    /**
     * Sets the body
     *
     * @param mixed $body Array or string
     *
     * @access public
     */
    function setBody($body)
    {
	if (is_array($body)) {
	    $this->body = implode("\r\n", $body);
	} else {
	    $this->body = $body;
	}
    }

    // }}}
    // {{{ getBody()

    /**
     * Gets the body
     *
     * @return string
     * @access public
     */
    function getBody()
    {
	return $this->body;
    }

    // }}}
    // {{{ splitMessage()

    /**
     * Splits the header and body given in $input apart (at the first
     * blank line) and return them (in an array) with the same type as $input.
     *
     * @param mixed $input Message in form of eiter string or array
     *
     * @return array Contains separated header and body sections in same type as $input
     * @access public
     */
    function splitMessage($input)
    {
	switch (true) {

	    // String
	    case is_string($input);
    		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $matches)) {
    		    return array('header' => $matches[1], 'body' => $matches[2]);
    		} else {
	    	    return PEAR::throwError('Could not split header and body');
		}
		break;
		
	    // Array
	    case is_array($input);
		$header = array();
		while (($line = array_shift($input)) != '') {
		    $header[] = $line;
		}
    		return array('header' => &$header, 'body' => $input);
		break;

	    // Unknown type
	    default:
	        return PEAR::throwError('Unsupported type: '.gettype($input));
	}
    }

    // }}}

}

// }}}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
