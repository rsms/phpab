<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage imap
 */
final class IMAP {
	
	/**
	 * =?utf-8?Q?Mikael_Berggren?=  ->  Mikael Berggren
	 * 
	 * @param  string  Encoded string
	 * @return string  Decoded string
	 */
	public static function mimeStringDecode( $str ) {
		$s = imap_mime_header_decode($str);
		$s[0]->text;
	}
}
?>
