<?
/**
 * PHP implementation of the TEA encryption algorithm.
 *
 * Optimised PHP code. (Still considered WIP)
 * Alot of APD profiling has been done.
 * 
 * Note that TEA has some weaknesses. The best available TEA algorithm
 * in Abstract Base is {@link XXTEA}.
 *
 * @todo Variable iteration is currently broken, only 32 n works. Fix this.
 *
 * @version    $Id$
 * @package    ab
 * @subpackage crypto
 * @author     Rasmus Andersson <http://hunch.se/>
 */
class TEACipherImpl extends Cipher {
	
	/** @var int */
	private $iterations = 32;
	
	/** @var long[] Raw, fixed length version of $secret */
	private $rawSecret;
	
	/** @var int */
	private $rawSecretSize;
	
	/** @var string */
	private static $CNULL = "\0";
	
	
	/**
	 * @param  string
	 * @param  int
	 * @see    setIterations()
	 * @see    getIterations()
	 */
	public function __construct($key) {
		$this->setKey($key);
	}
	
	
	/**
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException  if key is empty/null
	 */
	public function setKey($key)
	{
		if(!$key)
			throw new IllegalArgumentException('Key is empty');
		
		# resize key to a multiple of 128 bits/16 bytes. (TEA uses static key length of 128)
		$this->resizeData($key, 16, true);
		
		# Translate secret to array of longs
		$this->rawSecretSize = self::str2long($key, $this->rawSecret, 0);
	}
	
	/**
	 *  Encrypt a string
	 *
	 *  @param  string
	 *  @return string
	 */
	public function encrypt( $data )
	{
		// resize data to 32 bits (4 bytes)
		$n = $this->resizeData($data, 4);
		
		// convert data to long
		$data_long[0] = $n;
		$n_data_long  = self::str2long($data, $data_long, 1);
		
		
		// resize data_long to 64 bits (2 longs of 32 bits)
		$n = count($data_long);
		
		if (($n & 1) == 1)
		{
			$data_long[$n] = self::$CNULL;
			$n_data_long++;
		}
		
		
		// encrypt the long data with the key
		$enc_data = '';
		$w		  = array(0,0);
		$j		  = 0;
		$k		  = array(0,0,0,0);
		
		for ($i = 0; $i < $n_data_long; ++$i)
		{
			// get next key part of 128 bits
			if ($j + 4 <= $this->rawSecretSize) {
				$k[0] = $this->rawSecret[$j];
				$k[1] = $this->rawSecret[$j + 1];
				$k[2] = $this->rawSecret[$j + 2];
				$k[3] = $this->rawSecret[$j + 3];
			} else {
				$k[0] = $this->rawSecret[$j % $this->rawSecretSize];
				$k[1] = $this->rawSecret[($j + 1) % $this->rawSecretSize];
				$k[2] = $this->rawSecret[($j + 2) % $this->rawSecretSize];
				$k[3] = $this->rawSecret[($j + 3) % $this->rawSecretSize];
			}
			$j = ($j + 4) % $this->rawSecretSize;
			
			$this->encryptLong($data_long[$i], $data_long[++$i], $w, $k);
			
			// append the enciphered longs to the result
			$enc_data .= pack('N', $w[0]);
			$enc_data .= pack('N', $w[1]);
		}
		
		return $enc_data;
	}
	
	
	/**
	 *  Decrypt a string
	 *
	 *  @param  string  $data  Encrypted data to decrypt.
	 *  @return string         Binary decrypted character string.
	 *  @access public
	 *  @see decryptFile(), encrypt(), encryptFile()
	 */
	public function decrypt( $data )
	{	
		// convert data to long
		$n_enc_data_long = self::str2long($data, $enc_data_long, 0);
		
		// decrypt the long data with the key
		$data = '';
		$w = array(0, 0);
		$j = 0;
		$len = 0;
		$k = array(0, 0, 0, 0);
		$pos = 0;
		
		for ($i = 0; $i < $n_enc_data_long; $i += 2)
		{
			// get next key part of 128 bits
			if ($j + 4 <= $this->rawSecretSize) {
				$k[0] = $this->rawSecret[$j];
				$k[1] = $this->rawSecret[$j + 1];
				$k[2] = $this->rawSecret[$j + 2];
				$k[3] = $this->rawSecret[$j + 3];
			} else {
				$k[0] = $this->rawSecret[$j % $this->rawSecretSize];
				$k[1] = $this->rawSecret[($j + 1) % $this->rawSecretSize];
				$k[2] = $this->rawSecret[($j + 2) % $this->rawSecretSize];
				$k[3] = $this->rawSecret[($j + 3) % $this->rawSecretSize];
			}
			$j = ($j + 4) % $this->rawSecretSize;
			
			$this->decryptLong($enc_data_long[$i], $enc_data_long[$i + 1], $w, $k);
 			
			// append the deciphered longs to the result data (remove padding)
			if (0 == $i) {
				$len = $w[0];
				if (4 <= $len)
					$data .= pack('N', $w[1]);
				else
					$data .= substr(pack('N', $w[1]), 0, $len % 4);
			} else {
				$pos = ($i - 1) * 4;
				if ($pos + 4 <= $len) {
					$data .= pack('N', $w[0]);
					if ($pos + 8 <= $len)
						$data .= pack('N', $w[1]);
					elseif ($pos + 4 < $len)
						$data .= substr(pack('N', $w[1]), 0, $len % 4);
				} else
					$data .= substr(pack('N', $w[0]), 0, $len % 4);
			}
		}
		return $data;
	}
	
	
	/**
	 *  Encipher a single long (32-bit) value.
	 *
	 * @param  integer  32 bits of data
	 * @param  integer  32 bits of data
	 * @param  array    Placeholder for encrypted 64 bits (in w[0] and w[1])
	 * @param  array    128 bit secret (in k[0]-k[3])
	 * @return void
	 */
	protected function encryptLong($y, $z, &$w, &$k)
	{
		$sum   = 0;
		$delta = 0x9E3779B9;
		$n	   = $this->iterations;
		
		while ($n-- > 0) {
			$y = self::uintAdd($y, self::uintAdd($z << 4 ^ self::uintRShift($z, 5), $z) ^
									self::uintAdd($sum, $k[$sum & 3]));
			$sum = self::uintAdd($sum, $delta);
			$z = self::uintAdd($z, self::uintAdd($y << 4 ^ self::uintRShift($y, 5), $y) ^
									self::uintAdd($sum, $k[self::uintRShift($sum, 11) & 3]));
		}
		
		$w[0] = $y;
		$w[1] = $z;
	}
	
	
	/**
	 * Decrypt a single long (32-bit integer) value
	 *
	 * @param  integer  32 bits of enciphered data
	 * @param  integer  32 bits of enciphered data
	 * @param  array    Placeholder for deciphered 64 bits (in w[0] and w[1])
	 * @param  array    128 bit secret (in k[0]-k[3])
	 * @return void
	 */
	protected function decryptLong($y, $z, &$w, &$k)
	{
		// sum = delta<<5, in general sum = delta * n
		$sum = 0xC6EF3720;
		$delta = 0x9E3779B9;
		$n = $this->iterations;
		
		while ($n-- > 0) {
			$z = self::uintAdd($z, -(self::uintAdd($y << 4 ^ self::uintRShift($y, 5), $y) ^
										self::uintAdd($sum, $k[self::uintRShift($sum, 11) & 3])));
			$sum = self::uintAdd($sum, -$delta);
			$y = self::uintAdd($y, -(self::uintAdd($z << 4 ^ self::uintRShift($z, 5), $z) ^
										self::uintAdd($sum, $k[$sum & 3])));
		}
		
		$w[0] = $y;
		$w[1] = $z;
	}
	
	
	/**
	 *  Resize data string to a multiple of specified size.
	 *
	 *  @param  string  String to resize
	 *  @param  int     Bytes to align data to
	 *  @param  bool    Set to true if padded bytes should NOT be zero
	 *  @return int		Length of supplied data string
	 */
	protected function resizeData( &$data, $size, $notNull = false )
	{
		$n = strlen($data);
		$nmod = $n % $size;
		
		if( $nmod > 0 ) {
			if( $notNull ) {
				for ($i = $n; $i < $n - $nmod + $size; ++$i)
					$data{$i} = $data{$i % $n};
			} else {
				for ($i = $n; $i < $n - $nmod + $size; ++$i)
					$data{$i} = self::$CNULL;
			}
		}
		
		return $n;
	}
	
	
	/**
	 *  Convert string to array of longs.
	 *
	 *  @param  string
	 *  @param  long[]
	 *  @param  int
	 *  @return int     New value of $fromIndex
	 */
	private static function str2long(&$string, &$longs, $fromIndex = 0)
	{
		# This can be reversed using: string = pack('N', $longs)
		$n = strlen($string);
		$tmp = unpack('N*', $string);
		$tmpLen = count($tmp);
		
		foreach($tmp as $v)
			$longs[$fromIndex++] = $v;
		
		return $fromIndex;
	}
	
	
	/**
	 * Handle proper unsigned right shift, dealing with PHP's signed shift.
	 *
	 * @author  Rasmus Andersson <http://hunch.se/>
	 * @author  Jeroen Derks <jeroen@derks.it>
	 */
	private static function uintRShift($integer, $n)
	{
		// convert to 32 bits
		if (0xffffffff < $integer || -0xffffffff > $integer)
			$integer = fmod($integer, 0xffffffff + 1);
		
		// convert to unsigned integer
		if (0x7fffffff < $integer)
			$integer -= 0xffffffff + 1.0;
		elseif (-0x80000000 > $integer)
			$integer += 0xffffffff + 1.0;
		
		// do right shift
		if (0 > $integer) {
			$integer &= 0x7fffffff;		// remove sign bit before shift
			$integer >>= $n;			// right shift
			$integer |= 1 << (31 - $n);	// set shifted sign bit
		} else
			$integer >>= $n;			// use normal right shift
		
		return $integer;
	}
	
	
	/**
	 * Handle proper unsigned add, dealing with PHP's signed add.
	 *
	 * This is by far the most expensive logic in this class.
	 * During encryption/decryption it stands for 50-55% of the processing
	 * expenses. The uintRShift method is the next most expensive with
	 * it's 35-40% cost. This is due to fmod() calls and calculus using
	 * 32bit unsigned integers and double-precision floats. [ra]
	 *
	 * @author  Rasmus Andersson <http://hunch.se/>
	 * @author  Jeroen Derks <jeroen@derks.it>
	 */
	private static function uintAdd($i1, $i2)
	{
		$result = ($i1 < 0.0 ? $i1 - (1.0 + 0xffffffff) : $i1)
			+ ($i2 < 0.0 ? $i2 - (1.0 + 0xffffffff) : $i2);
		
		# convert to 32 bits (called approx 30% of every call to this method)
		if (0xffffffff < $result || -0xffffffff > $result)
			$result = fmod($result, 0xffffffff + 1);
		
		# convert to signed integer
		if (0x7fffffff < $result)
			$result -= 0xffffffff + 1.0;
		elseif (-0x80000000 > $result)
			$result += 0xffffffff + 1.0;
		
		return $result;
	}
	
	
	/** @ignore */
	public static function __test() {
		parent::__test(new self('abcdefghijklmnop'));
	}
}
?>