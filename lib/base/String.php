<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
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