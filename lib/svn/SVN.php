<?
/**
 * Subversion utilities
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
final class SVN {
	
	/** @var int seconds */
	public static $apcTTL = 60;
	
	/** @var bool */
	public static $apcEnabled = true;
	
	/** @var string */
	protected static $lookBin = '';
	
	/** @var string */
	protected static $grepBin = '';
	
	/**
	 * @param  string
	 * @return string
	 */
	public static function look($cmd) {
		$cmd = self::lookBin().' '.$cmd;
		return `$cmd`;
	}
	
	/** @return string */
	public static function grepBin() {
		if(!self::$grepBin)
			self::$grepBin = rtrim(`which grep`);
		return self::$grepBin;
	}
	
	/** @return string */
	private static function lookBin() {
		if(!self::$lookBin)
			self::$lookBin = rtrim(`which svnlook`);
		return self::$lookBin;
	}
	
	/** @var array */
	private static $scheduledCacheWrites = array();
	
	/** @var bool */
	private static $hasSchCacheTrig = false;
	
	/**
	 * Register data to be cached at end of request/response-session
	 *
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public static function scheduleCacheWrite($k, &$v) {
		if(!self::$hasSchCacheTrig) {
			register_shutdown_function(array('SVN','finalize'));
			self::$hasSchCacheTrig = true;
		}
		self::$scheduledCacheWrites[$k] =& $v;
	}
	
	/** @ignore */
	public static function finalize()
	{
		foreach(self::$scheduledCacheWrites as $k => $v)
		{
			$tmpfile = '/tmp/'.$k.'.lock';
			
			if(!($fp = fopen($tmpfile,'w')))
				throw new IOException("Failed to create lock file '$tmpfile'");
			
			flock($fp, LOCK_EX);
			# check AGAIN after we get the lock. Maybe someone was faster than us...
			if(apc_fetch($k) === false)
				apc_store($k, $v, SVN::$apcTTL);
			fclose($fp);
			@unlink($tmpfile);
		}
	}
}
SVN::$apcEnabled = function_exists('apc_fetch');
?>