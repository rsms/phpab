<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage base
 */
class Process {
	
	private static $resolvedBinariesCache = array();
	
	/**
	 * @param  string name
	 * @return string path
	 * @throws IllegalStateException if PHP is running in safe-mode
	 */
	public static function resolveBinary($name)
	{
		if(!isset(self::$resolvedBinariesCache[$name])) {
			if(AB::$isSafemode)
				throw new IllegalStateException('Safe-mode active. Unable to run command');
			$r = trim(`which "$name"`);
			if($r) {
				self::$resolvedBinariesCache[$name] = $r;
				return $r;
			}
			return $name;
		}
		return self::$resolvedBinariesCache[$name];
	}
	
	/**
	 * @param  string
	 * @return string  output
	 * @throws ProcessException
	 */
	public static function exec( $program /*, arg1, arg2, ...*/ )
	{
		if(isset(self::$resolvedBinariesCache[$program]))
			$program = self::$resolvedBinariesCache[$program];
		
		$program = $program;
		for($i=1;$i<func_num_args();$i++)
			$program .= ' ' . escapeshellarg(func_get_arg($i));
		$program .= ' 2>&1';
		
		try {
			exec($program, $out, $return_value);
			$out = trim(implode("\n", $out));
			
			if($return_value != 0)
				throw new ProcessException($out);
			
			return $out;
		}
		catch(PHPException $e) {
			$e->rethrow('ProcessException', 'exec');
		}
	}
}
?>