<?
/**
 * A Logger object is used to log messages for a specific system or application component.
 * 
 * Loggers are normally named, using a hierarchical dot-separated namespace. Logger names 
 * can be arbitrary strings, but they should normally be based on the package name or class 
 * name of the logged component, such as MyClass or my.application.
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage logging
 */
class Logger {
	
	const LEVEL_ALL   = -2147483647;
	const LEVEL_DEBUG = 0;
	const LEVEL_INFO  = 1;
	const LEVEL_WARN  = 2;
	const LEVEL_ERROR = 3;
	const LEVEL_FATAL = 4;
	const LEVEL_OFF   = 2147483647;
	
	
	/** @var Logger[] */
	static protected $loggers = array();
	
	/** @var string */
	static protected $basedir = '.';
	
	/** @var int */
	static protected $defaultLevel = 2;
	
	/** @var LogHandler */
	static protected $defaultHandler = null;
	
	/** @var string */
	protected $name;
	
	/** @var int */
	protected $level;
	
	/** @var bool */
	protected $printsName = true;
	
	/** @var LogHandler */
	protected $handler = null;
	
	/** @var LogFilter[] */
	protected $filters = array();
	
	
	/**
	 * Get or create a logger for a named facility
	 * 
	 * @param  string
	 * @return Logger
	 */
	public static function getLogger( $name )
	{
		if(!isset(self::$loggers[$name]))
			self::$loggers[$name] = new Logger($name);
		return self::$loggers[$name];
	}
	
	/**
	 * Default level used for new loggers w/o explicitly specified levels
	 * @return int
	 */
	public static function getDefaultLevel() {
		return self::$defaultLevel;
	}
	
	/**
	 * Set default level used for new loggers w/o explicitly specified levels
	 * @param  mixed  int or string
	 * @return void
	 */
	public static function setDefaultLevel( $level ) {
		if(is_int($level))
			self::$defaultLevel = $level;
		else
			self::$defaultLevel = self::levelValue($level);
	}
	
	/**
	 * Default handler used for new loggers
	 * @return LogHandler
	 */
	public static function getDefaultHandler() {
		return self::$defaultHandler;
	}
	
	/**
	 * Set default handler used for new loggers
	 * @param  LogHandler
	 * @return void
	 */
	public static function setDefaultHandler( LogHandler $handler ) {
		self::$defaultHandler = $handler;
	}
	
	/**
	 * Create a new Logger
	 *
	 * @param  string
	 * @param  int    Defaults to LEVEL_WARN
	 * @param  LogHandler
	 */
	protected function __construct( $name, $level = null, $handler = null )
	{
		$this->name = $name;
		
		if($handler != null)
			$this->setHandler($handler);
		else
			$this->setHandler(self::$defaultHandler);
		
		if(is_numeric($level))
			$this->level = intval($level);
		else
			$this->level = self::$defaultLevel;
	}
	
	
	/**
	 * Set logger for name. This should be used with care!
	 *
	 * @param  Logger
	 * @param  string
	 * @return void
	 */
	public static function setLogger( Logger $logger, $forName ) {
		self::$loggers[$forName] = $logger;
	}
	
	
	/**
	 * Load filters from cd context
	 * 
	 * @param  array
	 * @return void
	 */
	public function loadFilters( $filterConfigs )
	{	
		if(!is_array($filterConfigs))
			return;
		
		$this->filters = array();
		foreach($filterConfigs as $cfg)
		{	
			if(!isset($cfg['class']))
				throw new IllegalStateException('Failed to load log filter. Missing class parameter.');
			
			if(!class_exists($cfg['class']))
				throw new IllegalStateException('Failed to load log filter. Class not found: "' . $cfg['class'] . '"');
			
			$f = new $cfg['class'];
			unset($cfg['class']);
			$f->setParameters($cfg);
			
			$this->filters[] = $f;
		}
	}
	
	
	/**
	 * Get log filter configuration by classname
	 * 
	 * @param  string
	 * @return array
	 */
	public static function getFilterConfigForClass($classname) {
		foreach(cdCtx('log.filters') as $id => $cfg)
			if($cfg['class'] == $classname)
				return $cfg;
	}
	
	
	/**
	 * Print a FATAL message
	 *
	 * @param  mixed
	 * @return void
	 * @see    log()
	 */
	public function fatal( $msg ) { $this->logs($msg, self::LEVEL_FATAL); }
	
	/**
	 * Print a ERROR message
	 *
	 * @param  mixed
	 * @return void
	 * @see    log()
	 */
	public function error( $msg ) { $this->logs($msg, self::LEVEL_ERROR); }
	
	/**
	 * Print a WARN message
	 *
	 * @param  mixed
	 * @return void
	 * @see    log()
	 */
	public function warn( $msg ) { $this->logs($msg, self::LEVEL_WARN); }
	
	/**
	 * Print a INFO message
	 *
	 * @param  mixed
	 * @return void
	 * @see    log()
	 */
	public function info( $msg ) { $this->logs($msg, self::LEVEL_INFO); }
	
	/**
	 * Print a DEBUG message
	 *
	 * @param  mixed
	 * @return void
	 * @see    log()
	 */
	public function debug( $msg ) { $this->logs($msg, self::LEVEL_DEBUG); }
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param  bool
	 * @return void
	 */
	public function setDisplaysName( $yes ) {	
		$this->printsName = $yes;
	}
	
	/**
	 * @return bool
	 */
	public function getDisplaysName() {
		return $this->printsName;
	}
	
	/**
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}
	
	/**
	 * String representation of log level
	 * @return string
	 */
	public function getLevelName() {
		return self::levelName($this->level);
	}
	
	/**
	 * @param  int
	 * @return void
	 */
	public function setLevel( $level ) {
		$this->level = $level;
	}
	
	/**
	 * @return LogHandler
	 */
	public function getHandler() {
		return $this->handler;
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setHandler( LogHandler $handler ) {
		$this->handler = $handler;
	}
	
	
	/**
	 * Forward a message to the current <samp>{@link LogHandler}</samp>
	 *
	 * If <samp>$msg</samp> is an <samp>object</samp>, it will be converted to a string 
	 * using $obj->__toString()</samp>. If <samp>$msg</samp> is an <samp>Exception</samp>,
	 * it will be converted to a string using <samp>MException::format($e)</samp>. However,
	 * this is done by the LogHandler, so these are more or less guidelines than rules.
	 *
	 * @param  LogRecord
	 * @return void
	 */
	public function log( LogRecord $rec )
	{
		if($this->handler != null)
		{
			// don't log?
			if($rec->getLevel() < $this->level)
				return;
			
			// Filter
			foreach($this->filters as $filter) {
				try {
					if(!$filter->filter($rec))
						return;
				}
				catch(Exception $e) {
					$rec->setMessage($rec->getMessage() . ' [LogFilterException in ' 
						. get_class($filter) . '->filter(): ' . $e->getMessage() . '] ');
				}
			}
			
			// Publish
			$this->handler->publish($rec);
		}
	}
	
	
	/**
	 * String representation of log level
	 *
	 * @param  int
	 * @return string
	 */
	public static function levelName($level) {
		if    ($level < self::LEVEL_DEBUG) return 'ALL  ';
		elseif($level < self::LEVEL_INFO)  return 'DEBUG';
		elseif($level < self::LEVEL_WARN)  return 'INFO ';
		elseif($level < self::LEVEL_ERROR) return 'WARN ';
		elseif($level < self::LEVEL_FATAL) return 'ERROR';
		elseif($level < self::LEVEL_OFF)   return 'FATAL';
		return 'OFF  ';
	}
	
	/**
	 * String log level to integer (LEVEL_ constants) value
	 *
	 * @param  string  Case-insensitive
	 * @return int
	 */
	public static function levelValue($level) {
		switch(strtoupper($level)) {
			case 'DEBUG': return self::LEVEL_DEBUG;
			case 'INFO':  return self::LEVEL_INFO;
			case 'WARN':  return self::LEVEL_WARN;
			case 'ERROR': return self::LEVEL_ERROR;
			case 'FATAL': return self::LEVEL_FATAL;
		}
		return self::OFF;
	}
	
	/**
	 * @param  double  Hi-res Timestamp
	 * @return string  format <samp>date(FORMAT) . sprintf('%03.0f')</samp>
	 */
	public static function formatTime( $time, $format = 'Y-m-d H:i:s.' ) {
		$i = intval($time);
		$m = ($time-$i)*1000;
		if($m>999)
			return date($format, $i+1).'.0';
		else
			return date($format, $i).sprintf('%03.0f',$m);
	}
	
	
	/**
	 * @return void
	 */
	private function logs( $msg, $level ) {
		$this->log(new LogRecord($this, $msg, $level, microtime(1)));
	}
	
	
	/**
	 * @return string[]
	 */
	public function __sleep() {
		return array('name', 'level', 'msgPrefix', 'handler', 'filters');
	}
	
	
	/**
	 * Initialize the logger class, through setting the directory in which 
	 * file log handlers will put named log files.
	 * 
	 * @param  string  Writable directory
	 * @return void
	 */
	public static function init( $logsDir ) {
		self::$basedir = realpath($logsDir);
	}
}
Logger::setDefaultHandler(new ConsoleLogHandler('stderr'));
?>