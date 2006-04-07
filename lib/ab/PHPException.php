<?
/**
 * Thrown to indicate a PHP exception.
 * Converted from native functions non-fatal thrown errors/warnings.
 * @package    hunch.ab
 * @subpackage base
 */
class PHPException extends ABException {
	
	/**
	 * @param  string
	 * @return void
	 */
	public function rethrow($asClass /*, skip_func_name1, skip_func_name2, ...*/ )
	{
		if(func_num_args() > 1) {
			$rem = func_get_args();
			array_shift($rem);
			$this->stripFunctionNames($rem);
		}
		throw new $asClass($this);
	}
	
	/**
	 * @param  string[]
	 * @return void
	 */
	public function stripFunctionNames($func_names) {
		if($func_names)
			$this->setMessage(preg_replace('/^('.implode('|',$func_names).')\([^\)]*\): /', '', $this->getMessage()));
	}
}
?>