<?
/**
 * Readline-based console application
 *
 * <b>Work In Progress</b>
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage util
 */
class ReadlineConsoleApplication extends ConsoleApplication {
	
	public $running = true;
	public $stdin = null;
	public $readline_history = array();
	public $env_vars = array();
	public $output_prefix = '';
	
	/**
	 * @return int exit status
	 */
	public function main(&$argc, &$argv)
	{
		$this->openConsole();
		return 0;
	}
	
	/**
	 * @return void
	 */
	public function openConsole($input_prefix = 'php> ', $output_prefix = ' >>> ')
	{
		$this->stdin = fopen('php://stdin', 'rw');
		$this->output_prefix = $output_prefix;
		$old_errhandler = set_error_handler(array($this, 'handleUserError'));
		
		while($this->running)
		{
			try {
				$input = $this->readline($input_prefix);
				$output = $this->process($input);
				if($output !== '__no_console__input__')
					$this->printOut($this->varToString($output));
			}
			catch(Exception $e) {
				print get_class($e) . ': ' . $e->getMessage() . "\n";
			}
		}
		
		set_error_handler($old_errhandler);
	}
	
	public function handleUserError($errno, $str, $file, $line)
	{
		if(error_reporting() == 0)
			return;
		
		if(ob_get_level())
			ob_end_flush();
		
		switch($errno) {
			case E_PARSE:
				$this->printOut('Parse Error: ' . $str);
				exit($errno);
			case E_USER_ERROR:
			case E_ERROR:
				$this->printOut('Fatal Error: ' . $str);
				exit($errno);
			case E_USER_WARNING:
			case E_WARNING:
				$this->printOut('Error: ' . $str);
			case E_NOTICE:
			case E_USER_NOTICE:
				$this->printOut('Warning: ' . $str);
		}
	}
	
	public function printOut($msg) {
		echo $this->output_prefix, $msg;
		if(substr($msg,-1) != "\n")
			print "\n";
	}
	
	public function varToString($v, $simple = false)
	{
		if(is_object($v)) {
			return get_class($v) . $v . "\n";
		}
		elseif(is_array($v)) {
			return $simple ? 'Array('.count($v).")\n" : print_r($v, 1);
		}
		elseif(is_int($v)) {
			return "int $v\n";
		}
		elseif(is_double($v)) {
			return "double $v\n";
		}
		elseif(is_float($v)) {
			return "float $v\n";
		}
		elseif(is_bool($v)) {
			return "bool ".($v ? 'TRUE' : 'FALSE')."\n";
		}
		elseif(is_string($v)) {
			return "string(" . strlen($v) . ") ".var_export($v, 1)."\n";
		}
		else {
			if($simple)
				return gettype($v)."\n";
			ob_start();
			var_dump($v);
			return ob_get_clean();
		}
	}
	
	public function readline($prefix = '')
	{
		print $prefix;
		flush();
		
		# 27 91 65 = UP
		# 27 91 66 = DOWN
		
		$line = rtrim(fgets($this->stdin, 128000));
		$this->readline_history[] = $line;
		
		return $line;
	}
	
	/**
	 * @param  string  input
	 * @return mixed   result
	 */
	public function process($console_input)
	{
		if(!$console_input)
			return '__no_console__input__';
		
		if($console_input{0} == '.')
		{
			$argv = preg_split('/\s+/', substr($console_input,1));
			$output = $this->outputCommand(strtolower(array_shift($argv)), $argv);
			return ($output == null) ? '__no_console__input__' : $output;
		}
		else {
			return $this->outputEval($console_input);
		}
	}
	
	/**
	 * @param  string  command name
	 * @param  array   arguments
	 * @return mixed
	 */
	public function outputCommand($cmd, $argv) {
		return null;
	}
	
	/**
	 * @param  string  input
	 * @return mixed   result
	 */
	public function outputEval($console_input)
	{
		$__pre_vars__ = get_defined_vars();
		
		foreach($this->env_vars as $k => $v)
			eval("\$$k =& \$this->env_vars['$k'];");
		
		$__ret__ = null;
		
		ob_start();
		eval('$__ret__ = ' . $console_input . (substr(ltrim($console_input), -1) != ';' ? ';' : ''));
		$__out__ = ob_get_clean();
		if(substr(trim($__out__), 0, 13) == 'Parse error: ') {
			$this->printOut(substr(trim($__out__), 0, strrpos($__out__, ' in ')));
			return '__no_console__input__';
		}
		
		# save new env_vars
		foreach(array_diff_assoc(get_defined_vars(), $__pre_vars__) as $k => $v) {
			if(!isset($this->env_vars[$k]) && $k != '__out__' && $k != '__ret__' && $k != '__pre_vars__')
				$this->env_vars[$k] = $v;
		}
		
		return $__out__ ? $__out__ : $__ret__;
	}
}
?>