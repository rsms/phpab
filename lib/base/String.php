<?
/**
 * String utilities
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage util
 */
class String {
	
	
	/**
	 * Cut a string if it's longer than <samp>$maxlength</samp>
	 *
	 * Example:
	 *    cutString('abcdefghijk', 7)  // abcd...
	 *    cutString('abcdef', 7)       // abcdef
	 *
	 * @param  string
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function cut($str, $maxlength, $suffix='...')
	{
		if(strlen($str) > $maxlength)
			return substr($str, 0, $maxlength - strlen($suffix)) . $suffix;
		return $str;
	}
	
	/**
	 * Constrain a string if it's longer than <samp>$maxlength</samp>
	 *
	 * Example:
	 *    constrainString('abcdefghijk', 8)  // abc...jk
	 *    constrainString('abcdef', 7)       // abcdef
	 *
	 * @param  string
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function constrain($str, $maxlength, $glue='...')
	{
		if(strlen($str) > $maxlength) {
			$length = $maxlength - strlen($glue);
			$a = ceil($length/2);
			$b = floor($length/2);
			return (($a > 0) ? substr($str, 0, $a) : '') . $glue . ($b > 0 ? substr($str, -floor($length/2)) : '');
		}
		return $str;
	}
	
	/**
	 * Find first char in string not matching character $ch
	 * 
	 * @param  string
	 * @param  char
	 * @param  int
	 * @return int  position or -1 if not found
	 */
	public static function indexOfNotMatchingChar($str, $ch, $offset=0)
	{	
		$len = strlen($str);
		for(;$offset<$len;$offset++)
			if($str{$offset} != $ch)
				return $offset;
		return -1;
	}
}
?>