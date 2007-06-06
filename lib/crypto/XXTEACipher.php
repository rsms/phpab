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
 * XXTEA cipher
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson {@link http://hunch.se/}
 * @author     Ma Bingyao(andot@ujn.edu.cn)
 * @see        TEA
 * @see        XTEA
 */
class XXTEACipher extends Cipher {
	
	const DELTA = 0x9E3779B9;
	
	/** @var array */
	protected $k;
	
	/** @param string */
	public function __construct($key=null) {
		if($key)
			$this->setKey($key);
	}
	
	/**
	 * Set key.
	 * Must be exactly 16 characters long. Will be clipped if longer or
	 * an exception is throws, in case the key is shorter than 16 chars.
	 * 
	 * @param  string
	 * @return void
	 * @throws Exception
	 */
	public function setKey($key) {
		if(strlen($key) < 16)
			throw new Exception('Key must be exactly 128 bits/16 chars long');
		if(strlen($key) > 16)
			$key = substr($key, 0, 16);
		$this->k = self::str2long($key, false);
		if (count($this->k) < 4)
	        for ($i = count($this->k); $i < 4; $i++)
	            $this->k[$i] = 0;
	}
	
	/**
	 * Encrypt data.
	 * 
	 * @param  string  Plain bytes
	 * @return string  Encrypted bytes
	 * @throws Exception
	 */
	public function encrypt($str) {
	    if ($str == '')
	        return '';
		if(!$this->k)
			throw new Exception('Key is undefined');
			
	    $v = self::str2long($str, true);
	    $n = count($v) - 1;

	    $z = $v[$n];
	    $y = $v[0];
	    $q = floor(6 + 52 / ($n + 1));
	    $sum = 0;
	    while (0 < $q--) {
	        $sum = self::int32($sum + self::DELTA);
	        $e = $sum >> 2 & 3;
	        for ($p = 0; $p < $n; $p++) {
	            $y = $v[$p + 1];
	            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($this->k[$p & 3 ^ $e] ^ $z));
	            $z = $v[$p] = self::int32($v[$p] + $mx);
	        }
	        $y = $v[0];
	        $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($this->k[$p & 3 ^ $e] ^ $z));
	        $z = $v[$n] = self::int32($v[$n] + $mx);
	    }
	    return self::long2str($v, false);
	}
	
	/**
	 * Decrypt data.
	 * 
	 * @param  string  Encrypted bytes
	 * @return string  Plain bytes
	 * @throws Exception
	 */
	public function decrypt($str) {
	    if ($str == '')
	        return '';
		if(!$this->k)
			throw new Exception('Key is undefined');
		
	    $v = self::str2long($str, false);
	    $n = count($v) - 1;

	    $z = $v[$n];
	    $y = $v[0];
	    $q = floor(6 + 52 / ($n + 1));
	    $sum = self::int32($q * self::DELTA);
	    while ($sum != 0) {
	        $e = $sum >> 2 & 3;
	        for ($p = $n; $p > 0; $p--) {
	            $z = $v[$p - 1];
	            $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($this->k[$p & 3 ^ $e] ^ $z));
	            $y = $v[$p] = self::int32($v[$p] - $mx);
	        }
	        $z = $v[$n];
	        $mx = self::int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($this->k[$p & 3 ^ $e] ^ $z));
	        $y = $v[0] = self::int32($v[0] - $mx);
	        $sum = self::int32($sum - self::DELTA);
	    }
	    return self::long2str($v, true);
	}
	
	/**
	 * Converts a serie of longs in BE/network byte order into a string
	 *
	 * @param  array
	 * @param  bool
	 * @return string
	 */
	protected static function long2str($v, $w=false) {
		$len = count($v);
		$s = array();
		for ($i = 0; $i < $len; $i++)
			$s[$i] = pack("N", $v[$i]);
		return $w ? substr(join('', $s), 0, $v[$len - 1]) : join('', $s);
	}
	
	/**
	 * Converts a string to a serie of longs in BE/network byte order
	 *
	 * @param  string
	 * @param  bool
	 * @return array
	 */
	protected static function str2long($s, $w=false) {
	    $v = unpack("N*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
	    $v = array_values($v);
	    if ($w)
	        $v[count($v)] = strlen($s);
	    return $v;
	}
	
	/**
	 * @param  int   16 or 32 bit integer
	 * @return long  32 bit integer
	 */
	protected static function int32($n) {
	    while ($n >= 2147483648) $n -= 4294967296;
	    while ($n <= -2147483649) $n += 4294967296; 
	    return (int)$n;
	}
	
	/**
	 * Unit test
	 * @ignore
	 */
	public static function __test() {
		$x = new self('abcdefghijklmnop');
		assert(base64_encode($x->encrypt('Hello')) == 'aCVjUPXOAknwRDyq');
		assert($x->decrypt(base64_decode('aCVjUPXOAknwRDyq')) == 'Hello');
	}
}
?>