<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage xml
 */
final class XML {
	
	private static $xtbl_u = array('&','"','<','>');
	private static $xtbl_e = array('&#38;','&#34;','&#60;','&#62;');
	private static $xtblt_u = array('&','<','>');
	private static $xtblt_e = array('&#38;','&#60;','&#62;');
	
	/**
	 * Escape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @see	   unescape()
	 */
	public static function escape($str) {
		return str_replace(self::$xtbl_u, self::$xtbl_e, $str);
	}
	
	/**
	 * Unescape attribute value
	 * 
	 * @param  string
	 * @return string
	 * @see	   escape()
	 */
	public static function unescape($str) {
		return str_replace(self::$xtbl_e, self::$xtbl_u, $str);
	}
	
	/**
	 * Escape text node
	 * 
	 * @param  string
	 * @return string
	 * @see	   unescapeText()
	 */
	public static function escapeText($str) {
		return str_replace(self::$xtblt_u, self::$xtblt_e, $str);
	}
	
	/**
	 * Unescape text node
	 * 
	 * @param  string
	 * @return string
	 * @see	   escapeText()
	 */
	public static function unescapeText($str) {
		return str_replace(self::$xtblt_e, self::$xtblt_u, $str);
	}
}
?>