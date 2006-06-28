<?
/**
 * Thrown to indicate a PHP exception.
 * Converted from native functions non-fatal thrown errors/warnings.
 * @package    ab
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
			$this->vstripFunctionNames($rem);
		}
		throw new $asClass($this);
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function stripFunctionNames($func1 /*, 'func2', 'func3', ... */) {
		$this->vstripFunctionNames(func_get_args());
	}
	
	/**
	 * @param  string[]
	 * @return void
	 */
	protected function vstripFunctionNames($func_names) {
		$this->setMessage(preg_replace('/^('.implode('|',$func_names).')\([^\)]*\): /', '', $this->getMessage()));
	}
}
?>