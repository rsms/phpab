<?
/**
 * A log message
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage logging
 */
class LogRecord
{	
	/** @var Logger */
	protected $logger;
	
	/** @var string */
	protected $message = null;
	
	/** @var Exception */
	protected $thrown = null;
	
	/** @var int */
	protected $level;
	
	/** @var double */
	protected $microtime;
	
	
	/**
	 * @param Logger
	 * @param string
	 * @param int
	 */
	public function __construct( Logger $parent, $message, $level, $microtime = 0.0 )
	{	
		if(is_object($message)) {
			if($message instanceof Exception)
				$this->thrown = $message;
			else
				$this->message = strval($message);
		}
		else
			$this->message = $message;
		
		$this->logger = $parent;
		$this->level = $level;
		$this->microtime = $microtime;
	}
	
	
	/** @return string or null if no message */
	public function getMessage() {
		return $this->message; }
	
	
	/**
	 * @param  string or null for none
	 * @return void
	 * @throws IllegalArgumentException if message is not a string nor null
	 */
	public function setMessage( $message ) {
		if(!is_string() && $message != null)
			throw new IllegalArgumentException('message must be a string or null');
		$this->message = $message;
	}
	
	
	/** @return int */
	public function getLevel() {
		return $this->level;
	}
	
	/**
	 * String representation of log level
	 * @return string
	 */
	public function getLevelName() {
		return Logger::levelName($this->level);
	}
	
	/** @return Exception or null if none */
	public function getThrown() {
		return $this->thrown; }
	
	
	/** @return Logger  or null if there is no Logger */
	public function getLogger() {
		return $this->logger; }
	
	
	/**
	 * Get event time in seconds, with microseconds, since 1970.
	 * 
	 * @return double
	 */
	public function getTime() {
		return $this->microtime; }
	
	
	/**
	 * @return string format <samp>date(FORMAT) . sprintf('%03.0f')</samp>
	 */
	public function getTimeFormat( $format = 'Y-m-d H:i:s.' ) {
		return Logger::formatTime($this->getTime(), $format);
	}
	
	
	/**
	 * String representation of this log record
	 * 
	 * @return string
	 */
	public function toString()
	{	
		// Date & Level
		$msg = '[' . $this->getTimeFormat() . ' ' . $this->getLevelName();
		
		// Name
		if($this->logger->getDisplaysName())
			$msg .= ' ' . $this->logger->getName();
		
		$msg .= '] ';
		
		// Message
		if($this->message)
			$msg .= $this->message.' ';
		if($this->thrown)
			$msg .= ABException::format($this->thrown, true, false);
		
		return "$msg";
	}
	
	/** @return string */
	public function __toString(){ return $this->toString();}
	
	
	/** @return string[] */
	/*public function __sleep() {
		return array('message', 'thrown', 'level', 'microtime'); }*/
}
?>