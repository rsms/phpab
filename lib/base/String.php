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
class String
{
	/** @var string */
	public static $alpha = '';
	
	/** @var array */
	public static $alpha_key = array();
	
	/** @var array */
	public static $blocksizes = array();
	
	/**
	 * @param  int
	 * @return int the block size for the pseudo-base-N encoding.
	 */
	public static function setBaseConversionSpan($alpha)
	{
		self::$alpha = $alpha;
		$hibase = strlen(self::$alpha);
		
		# calc block sizes
		self::$blocksizes = array(0, $hibase);
		for($base=2; $base < $hibase; $base++)
		{
			$blocksize = 0;
			$x = 0xffffffff;
			while ($x) {
				++$blocksize;
				$x = floor($x/$base);
			}
			self::$blocksizes[] = $blocksize;
		}
		
		# calc alpha-to-position map
		self::$alpha_key = array();
		for($base=0; $base < $hibase; $base++)
			self::$alpha_key[self::$alpha{$base}] = $base;
	}
	
	/**
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function baseEncode($base, $data)
	{
		if($base < 1)
			throw new IllegalArgumentException('$base must be a positive integer');
		
		if ($base >= strlen(self::$alpha))
			throw new IllegalArgumentException('$base must be lower than '.strlen(self::$alpha));
		
		if(!$data)
			return null;
		
		# Each four-byte block of MESSAGE is encoded as $blocksize symbols in
		# base N. We encode a three-byte block as $blocksize - 1 symbols, two-byte
		# as $blocksize - 2, etc.
		$l = strlen($data);
		$blocksize = self::$blocksizes[$base];
		
		$res = '';
		for ($i = 0; $i < $l; $i += 4) {
			$nin = ($l - $i > 4) ? 4 : $l - $i;
			$nout = $blocksize;
			# PHP doesn't, would you believe it, have unsigned ints, and in fact
			# ignores unpack()ing with unsigned values. Genius.
			$val = (float)0;
			for ($j = 0; $j < $nin; ++$j) {
				$val *= 256; # Shifting forces a cast to integer. Genius again.
				$val += ord($data{$i+$j});
			}
			$nout -= (4 - $nin);
			
			$r = '';
			while ($val) {
				$rem = fmod($val, $base); # Of *course* % is integer only! Genius thrice.
				if ($rem<0)
					$rem += $base;
				$val = floor($val / $base);
				$r .= self::$alpha{$rem};
			}
			
			# pad to block size.
			if (strlen($r) != $nout)
				$r .= str_repeat(self::$alpha{0}, ($nout - strlen($r)));
			
			#assert('strlen($r) <= $nout');
			$res .= strrev($r);
		}
		
		return $res;
	}
	
	/**
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function baseDecode($base, $data)
	{
		if ($base < 1)
			throw new IllegalArgumentException('$base must be a positive integer');
		
		if (!$data)
			return null;

		$blocksize = self::$blocksizes[$base];
		
		$res = '';
		$l = strlen($data);
		for ($i = 0; $i < $l; $i += $blocksize) {
			$nin = ($l - $i > $blocksize) ? $blocksize : $l - $i;
			$nout = 4;
			if ($nin < $blocksize) {
				$nout -= $blocksize - $nin;
				if ($nout < 0) return null;
			}
			
			$val = 0;
			for ($j = 0; $j < $nin; ++$j) {
				$val *= $base;
				$c = $data{$i+$j};
				if (!isset(self::$alpha_key[$c]))
					return null;
				$val += self::$alpha_key[$c];
			}
			
			$r = pack('N', $val);
			if ($nout < 4)
				$r = substr($r, -$nout);
			
			$res .= $r;
		}
		
		return $res;
	}
	
	/**
	 * Encode all bytes using decimal value entities.
	 * 
	 * @param  string
	 * @return string
	 */
	public static function valueEntitiyEncode($string)
	{
		$r = '';
		$len = strlen($string);
		for($i=0;$i<$len;$i++) {
			$n = ord($string{$i});
			if($n < 100)
				$r .= '&#0'.$n.';';
			else
				$r .= '&#'.$n.';';
		}
		return $r;
	}
	
	/**
	 * @ignore
	 * @return void
	 */
	public static function __test()
	{
		#print "\n".self::baseEncode(62, md5(__FILE__,true))."\n";
		$org = 'Hello World';
		for($base=2;$base<strlen(self::$alpha);$base++)
			assert(self::baseDecode($base, self::baseEncode($base, $org)) == $org);
	}
	
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

String::setBaseConversionSpan('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
?>