<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage base
 */

/**
 * Content Daemon base exception, thrown by anything related to contentd
 *
 * @package    hunch.ab
 * @subpackage base
 */
class MException extends Exception
{	
	/**
	 * @param  int
	 * @param  string
	 * @param  string
	 * @param  int
	 */
	public function __construct($msg = null, $errno = 0, $file = null, $line = -1)
	{	
		if($msg instanceof Exception) {
			$line = $msg->getLine();
			$file = $msg->getFile();
			$errno = $msg->getCode();
			$msg = $msg->getMessage();
		}
		parent::__construct($msg, $errno);
		if($file != null) $this->file = $file;
		if($line != -1)   $this->line = $line;
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setMessage($msg) { $this->message = $msg; }
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setFile($msg) { $this->file = $file; }
	
	/**
	 * @param  int
	 * @return void
	 */
	public function setLine($msg) { $this->line = intval($line); }
	
	/**
	 * Render a full HTML description of an exception
	 *
	 * @param  Exception
	 * @param  bool       Include call trace in the output
	 * @param  bool       Return nicely formatted HTML instead of plain text
	 * @param  string[]   An array of function (or Class::method) names to remove from trace 
	 *                    prior to rendering it. Specify null to disable.
	 *                    See {@link formatTrace()} for more information.
	 * @return string
	 * @see    formatTrace()
	 */
	public static function format( Exception $e, $includingTrace = true, $html = true, $skip = null )
	{
		if($html)
		{
			$str = '<div class="exception"><b>' .  get_class($e) . '</b><br /> '
				. '<span class="message">'.nl2br(htmlentities($e->getMessage())).'</span> '
				. '<span class="file">on line '.$e->getLine()
				. ' in '.Utils::relativePath($e->getFile(), AB::$dir)."</span>";
		}
		else {
			$str = get_class($e) . ': ' . $e->getMessage() . ' on line ' . $e->getLine()
				. ' in ' . Utils::relativePath($e->getFile(), AB::$dir);
		}
		
		if($includingTrace)
			$str .= "\n" . self::formatTrace($e, $html, $skip);
		
		return $html ? $str . '</div>' : $str;
	}
	
	/**
	 * Render a nice output of a backtrace from an exception
	 * 
	 * <b>The skip parameter</b>
	 *   - To skip a plain function, simply specify the function name. i.e. "__errorhandler"
	 *   - To skip a class or instance method, specify "Class::methodName"
	 * 
	 * @param  Exception
	 * @param  bool       Include call trace in the output
	 * @param  bool       Return nicely formatted HTML instead of plain text
	 * @param  string[]   An array of function (or Class::method) names to remove from trace 
	 *                    prior to rendering it. Specify null to disable.
	 * @return string
	 * @see    format()
	 */
	public static function formatTrace( Exception $e, $html = true, $skip = null )
	{
		$trace =& $e->getTrace();
		$traceLen = count($trace);
		$str = '';
		
		if($traceLen > 0)
		{
			if($html)
				$str .= "<div class=\"trace\"><pre>";
			
			if(is_array($skip)) {
				$traceTmp = $trace;
				$trace = array();
				foreach($traceTmp as $i => $ti) {
					if(in_array($ti['function'], $skip))
						continue;
					if(isset($ti['type']))
						if(in_array($ti['class'].'::'.$ti['function'], $skip))
							continue;
					$trace[] = $ti;
				}
			}
			
			$noSpace = strlen(strval($traceLen))+3;
			foreach($trace as $i => $ti)
			{
				$args = '()';
				if(isset($ti['args'])) {
					$argsCnt = count($ti['args']);
					if($argsCnt > 0)
						$args = '('.$argsCnt.')';
				}
				
				$str .= sprintf("  % {$noSpace}s ", $traceLen-$i);
				
				if(isset($ti['type']))
					$str .= $ti['class'].$ti['type'];
				else
					$str .= '::';
				$str .= $ti['function'].$args;
				
				if(isset($ti['line']))
					$str .= ' on line '.$ti['line'];
				if(isset($ti['file']))
					$str .= ' in ' . Utils::relativePath($ti['file'], AB::$dir);

				$str .="\n";
			}
			$str .= $html ? "</pre></div>\n" : "\n";
		}
		return trim($str,"\n")."\n";
	}
	
	
	/** @return string */
	public function toHTML() { return self::format($this); }
	
	/** @return string */
	public function toString() { return self::format($this, false, false); }
	
	/** @return string */
	public function __toString() { return $this->toString(); }
}


/**
 * Thrown to indicate a I/O exception
 * @package    hunch.ab
 * @subpackage io
 */
class IOException extends MException {}
	
	/**
	 * Thrown to indicate a connection exception
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class ConnectException extends IOException {}
	
	/**
	 * Thrown to indicate an auth error
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class AuthenticationException extends IOException {}
	
	/**
	 * Thrown to indicate that a file or directory can not be found
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class FileNotFoundException extends IOException {}
	
	/**
	 * Thrown to indicate a serialization process failed
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class SerializationException extends IOException {}
	
	/**
	 * Signals that a timeout has occurred on a socket read or accept.
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class SocketTimeoutException extends IOException {}
	
	/**
	 * Thrown to indicate an unexpected HTTP response/request
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class HttpException extends IOException {}
	
	/**
	 * @package    hunch.ab
	 * @subpackage io
	 */
	class DatabaseException extends IOException {}
	
		/**
		 * @package    hunch.ab
		 * @subpackage io
		 */
		class MySQLException extends DatabaseException {}


/**
 * Thrown to indicate an parsing error
 * @package    hunch.ab
 * @subpackage base
 */
class ParseException extends MException {}

	/**
	 * Thrown to indicate an XML parsing error
	 * @package    hunch.ab
	 * @subpackage base
	 */
	class XmlParserException extends ParseException {}

/**
 * Thrown to indicate that a method has been passed an illegal or inappropriate argument.
 * @package    hunch.ab
 * @subpackage base
 */
class IllegalArgumentException extends MException {}
	
	/**
	 * Thrown to indicate errornous format
	 * @package    hunch.ab
	 * @subpackage base
	 */
	class IllegalFormatException extends IllegalArgumentException {}
	
	/**
	 * @package    hunch.ab
	 * @subpackage base
	 */
	class IllegalTypeException extends IllegalArgumentException {}


/**
 * Signals that a method has been invoked at an illegal or inappropriate time.
 * @package    hunch.ab
 * @subpackage base
 */
class IllegalStateException extends MException {}

/**
 * Indicates a classcast gone wrong
 * @package    hunch.ab
 * @subpackage base
 */
class ClassCastException extends MException {}

/**
 * Thrown to indicate a configuration error
 * @package    hunch.ab
 * @subpackage base
 */
class ConfigurationException extends MException {}

/**
 * Thrown to indicate a PHP exception.
 * Converted from native functions non-fatal thrown errors/warnings.
 * @package    hunch.ab
 * @subpackage base
 */
class PHPException extends MException {
	/**
	 * @param  string
	 * @return void
	 */
	public function rethrow($asClass /*, skip_func_name1, skip_func_name2, ...*/ )
	{
		if(func_num_args() > 1) {
			$rem = func_get_args();
			array_shift($rem);
			$this->setMessage(preg_replace('/^('.implode('|',$rem).')\([^\)]*\): /', '', $e->getMessage()));
		}
		throw new $asClass($e);
	}
}

/**
 * @package    hunch.ab
 * @subpackage base
 */
class ProcessException extends MException {}

?>