<?
/**
 * Abstract Base exception
 *
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage base
 */
class ABException extends Exception
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
		$file = Utils::relativePath($e->getFile(), AB::$basedir);
		if($file{0} != '/')
			$file = '/'.$file;
		
		if($html)
		{
			$str = '<div class="exception"><b>' .  get_class($e) . '</b><br /> '
				. '<span class="message">'.nl2br(htmlentities($e->getMessage()));
			
			if($e instanceof PDOException)
				$str .= '<pre>' . trim(htmlentities($e->errorInfo)).'</pre>';
			
			$str .= '</span> '
				. '<span class="file">on line '.$e->getLine()
				. ' in '.$file."</span>";
		}
		else {
			$str = get_class($e) . ': ' . $e->getMessage();
			
			if($e instanceof PDOException)
				$str .= "\n".trim($e->errorInfo)."\n";
			
			$str .= ' on line ' . $e->getLine() . ' in ' . $file;
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
				if(isset($ti['file'])) {
					$file = Utils::relativePath($ti['file'], AB::$basedir);
					if($file{0} != '/')
						$file = '/'.$file;
					$str .= ' in ' . $file;
				}

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
?>