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
	protected static $lookPath = '';
	
	/**
	 * @param  string
	 * @return string
	 */
	public static function look($cmd) {
		$cmd = self::lookPath().' '.$cmd;
		return `$cmd`;
	}
	
	/** @return string */
	private static function lookPath() {
		if(!self::$lookPath)
			self::$lookPath = rtrim(`which svnlook`);
		return self::$lookPath;
	}
}
SVN::$apcEnabled = function_exists('apc_fetch');
?>