<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class Process
{
	/** @const hang up */
	const SIGHUP = 1;
	
	/** @const interrupt */
	const SIGINT = 2;
	
	/** @const quit */
	const SIGQUIT = 3;
	
	/** @const abort */
	const SIGABRT = 6;
	
	/** @const non-catchable, non-ignorable kill */
	const SIGKILL = 9;
	
	/** @const alarm clock */
	const SIGALRM = 14;
	
	/** @const software termination signal */
	const SIGTERM = 15;
	
	/** @var array */
	private static $resolvedBinariesCache = array();
	
	/**
	 * @param  string name
	 * @return string path
	 * @throws IllegalStateException if PHP is running in safe-mode
	 */
	public static function resolveBinary($name)
	{
		if(!isset(self::$resolvedBinariesCache[$name])) {
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
	 * @return string
	 */
	public static function escapeArg($argument) {
		return escapeshellarg($argument);
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
		
		for($i=1;$i<func_num_args();$i++)
			$program .= ' ' . func_get_arg($i);
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
	
	/**
	 * @param  string
	 * @param  array
	 * @param  array
	 * @param  string
	 * @param  bool
	 * @return Process
	 * @throws IOException
	 */
	public static function open($cmd, $args=array(), $env=null, $workingdir=null, $binary=false)
	{
		$descriptorspec = array(
			0 => array('pipe', 'r'),
		   1 => array('pipe', 'w'),
		   2 => array('pipe', 'w')
		);
		
		if(!$workingdir)
			$workingdir = '/tmp';
		
		$ops = array();
		if($binary)
			$ops['binary_pipes'] = true;
		
		if($args)
			$cmd .= ' '. implode(' ',array_map('escapeshellarg', $args));
		#var_dump($cmd);exit(0);
		
		if(!is_resource($ps = proc_open($cmd, $descriptorspec, $pipes, $workingdir, $env, $ops)))
			throw new ProcessException('failed to start process');
		
		return new self($ps, $pipes[0], $pipes[1], $pipes[2]);
	}
	
	/** @var resource */
	public $ps;
	
	/** @var resource */
	public $stdin;
	
	/** @var resource */
	public $stdout;
	
	/** @var resource */
	public $stderr;
	
	/**
	 * @param  resource
	 * @param  resource
	 * @param  resource
	 * @param  resource
	 */
	protected function __construct($ps, $stdin, $stdout, $stderr)
	{
		$this->ps = $ps;
		$this->stdin = $stdin;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
	}
	
	/**
	 * @return void
	 */
	public function __destruct()
	{
		try {
			if($this->close() != 0)
				$this->terminate(true);
		}catch(Exception $e){}
	}
	
	/**
	 * @param  int|string
	 * @return int If the signal caused process termination, this is
	 *             the status of the process that was run.
	 */
	public function signal($signal)
	{
		proc_terminate($this->ps, $signal);
	}
	
	/**
	 * @param  bool If true, SIGKILL (9) signal will be used to force-terminate 
	 *              the process. If false, SIGTERM (15) is used.
	 * @return int  If the signal caused process termination, this is
	 *              the status of the process that was run.
	 */
	public function terminate($force=false)
	{
		return $this->signal($signal);
	}
	
	/**
	 * Retrieve status about the process.
	 * 
	 * See http://php.net/proc_get_status for details.
	 * 
	 * @param  string Only retrieve $key status information.
	 * @return mixed  If $key is null, all status information is returned as an array.
	 */
	public function status($key=null)
	{
		$st = proc_get_status($this->ps);
		return ($key !== null) ? (isset($st[$key]) ? $st[$key] : null) : $st;
	}
	
	/**
	 * @return bool
	 */
	public function isRunning()
	{
		if(!is_resource($this->ps))
			return false;
		return $this->status('running') ? true : false;
	}
	
	/**
	 * @return int exit status
	 */
	public function close()
	{
		@fclose($this->stdin);
		@fclose($this->stdout);
		@fclose($this->stderr);
		return proc_close($this->ps);
	}
}
?>