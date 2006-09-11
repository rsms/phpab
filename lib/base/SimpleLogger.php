<?
/**
 * Simple logger, writes messages to a file pointer.
 *
 * Example of stderr logger:
 * <code>
 * $logger = new SimpleLogger(fopen('php://stderr', 1));
 * $logger->warn('You are a monkey');
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage util
 */
class SimpleLogger {
	
	const LEVEL_ALL   = -2147483647;
	const LEVEL_DEBUG = 0;
	const LEVEL_INFO  = 1;
	const LEVEL_WARN  = 2;
	const LEVEL_ERROR = 3;
	const LEVEL_FATAL = 4;
	const LEVEL_OFF   = 2147483647;
	
	/** @var int */
	public $level = 0;
	
	/** @var resource */
	public $fd = 0;
	
	/** 
	 * Formatting template.
	 * Available keywords:
	 *   %date    Date in format YYYY-MM-DD
	 *   %time    Time in format HH:MM:SS.SSS
	 *   %level   Level name. i.e. DEBUG
	 *   %message The message
	 *
	 * @var string
	 */
	public $format = "[%date %time %level] %message\n";
	
	/**
	 * If set to false, the string passed to the log methods are simply 
	 * written as-is, skipping formatting.
	 *
	 * @var bool
	 */
	public $enableFormatting = true;
	
	/**
	 * @param  resource
	 * @param  int
	 */
	public function __construct($outputFD, $level = 2) {
		$this->fd = $outputFD;
		$this->level = $level;
	}
	
	/** @ingore */
	public function __destruct() {
		@fclose($this->fd);
	}
	
	/**
	 * Print a FATAL message
	 *
	 * @param  mixed
	 * @return void
	 */
	public function fatal( $msg ) { $this->log($msg, self::LEVEL_FATAL); }
	
	/**
	 * Print a ERROR message
	 *
	 * @param  mixed
	 * @return void
	 */
	public function error( $msg ) { $this->log($msg, self::LEVEL_ERROR); }
	
	/**
	 * Print a WARN message
	 *
	 * @param  mixed
	 * @return void
	 */
	public function warn( $msg ) { $this->log($msg, self::LEVEL_WARN); }
	
	/**
	 * Print a INFO message
	 *
	 * @param  mixed
	 * @return void
	 */
	public function info( $msg ) { $this->log($msg, self::LEVEL_INFO); }
	
	/**
	 * Print a DEBUG message
	 *
	 * @param  mixed
	 * @return void
	 */
	public function debug( $msg ) { $this->log($msg, self::LEVEL_DEBUG); }
	
	/**
	 * Forward a message to the current <samp>{@link LogHandler}</samp>
	 *
	 * If <samp>$msg</samp> is an <samp>object</samp>, it will be converted to a string 
	 * using $obj->__toString()</samp>. If <samp>$msg</samp> is an <samp>Exception</samp>,
	 * it will be converted to a string using <samp>ABException::format($e)</samp>. However,
	 * this is done by the LogHandler, so these are more or less guidelines than rules.
	 *
	 * @param  LogRecord
	 * @return void
	 */
	public function log( $msg, $level )
	{
		// don't log?
		if($level < $this->level)
			return;
		
		// format
		if($this->enableFormatting)
		{
			$levelName = 'OFF  ';
			if    ($level < self::LEVEL_DEBUG) $levelName = 'ALL';
			elseif($level < self::LEVEL_INFO)  $levelName = 'DEBUG';
			elseif($level < self::LEVEL_WARN)  $levelName = 'INFO';
			elseif($level < self::LEVEL_ERROR) $levelName = 'WARN';
			elseif($level < self::LEVEL_FATAL) $levelName = 'ERROR';
			elseif($level < self::LEVEL_OFF)   $levelName = 'FATAL';
			
			$k = array('%level', '%message', '%date', '%time');
			$v = array($levelName, $msg, date('Y-m-d'), date('H:i:s.').sprintf('% -3d', intval((microtime(1)-time())*1000)) );
			$msg = str_replace($k, $v, $this->format);
		}
		
		// publish
		fwrite($this->fd, $msg);
	}
}
?>