<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/**
 * Abstract Base exception
 *
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage base
 */
class ABException extends Exception
{	
	/** @var Exception */
	public $cause = null;
	
	/**
	 * Create a new ABException
	 *
	 * The constructor has three different combinations:
	 *   - <samp>new ABException('A message', 12)</samp> Creates a new exception with the message "A message" and error code 12
	 *   - <samp>new ABException($another_exception)</samp> Creates a copy of <samp>$another_exception</samp> using the specified class.
	 *   - <samp>new ABException($caused_by_exception, 'No post found')</samp> Creates a new exception and sets <samp>$exception->cause</samp> to <samp>$caused_by_exception</samp>
	 *
	 * @param  mixed   <samp>string message</samp> or <samp>Exception cause</samp> or <samp>Exception inherith_from</samp>
	 * @param  mixed   <samp>int errno</samp> or <samp>string message</samp>
	 * @param  string
	 * @param  int
	 */
	public function __construct($msg = null, $errno = 0, $file = null, $line = -1, $cause = null)
	{	
		if($msg instanceof Exception) {
			if(is_string($errno) && $file == null && $line == -1 && $cause == null) {
				$this->cause = $msg;
				$msg = $errno;
				$errno = 0;
			}
			else {
				$line = $msg->getLine();
				$file = $msg->getFile();
				$errno = $msg->getCode();
				$msg = $msg->getMessage();
				if(isset($msg->errorInfo))
					$this->errorInfo = $msg->errorInfo;
			}
		}
		parent::__construct($msg, $errno);
		if($file != null)  $this->file = $file;
		if($line != -1)    $this->line = $line;
		if($cause != null) $this->cause = $cause;
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
	 * Convenience method equivalent to calling ABException::format with $html=true.
	 * 
	 * @param  Exception
	 * @param  bool
	 * @param  string[]
	 * @return string
	 * @see    format()
	 * @see    formatPlain()
	 */
	public static function formatHtml(Exception $e, $includingTrace=true, $skip=null)
	{
		return self::format($e, $includingTrace, true, $skip);
	}
	
	/**
	 * Convenience method equivalent to calling ABException::format with $html=false.
	 * 
	 * @param  Exception
	 * @param  bool
	 * @param  string[]
	 * @return string
	 * @see    format()
	 * @see    formatHtml()
	 */
	public static function formatPlain(Exception $e, $includingTrace=true, $skip=null)
	{
		return self::format($e, $includingTrace, false, $skip);
	}
	
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
		$code = $e->getCode();
		if($html)
		{
			$str = '<div class="exception"><b>' .  get_class($e) . ($code ? " [$code]":'') . '</b><br /> '
				. '<span class="message">'.nl2br(htmlentities($e->getMessage()));
			
			# extra info, used by PDOException, ActionDBException, etc
			if(isset($e->errorInfo) && $e->errorInfo)
			{
				$errorInfo = is_array($e->errorInfo) ? trim(print_r($e->errorInfo,1)) : strval($e->errorInfo);
				$str .= '<pre>' . trim(htmlentities(preg_replace('/[ \r\n\t]+/', ' ', $errorInfo))).'</pre>';
			}
			
			$str .= '</span> <span class="file">on line '.$e->getLine().' in '.$e->getFile().'</span>';
		}
		else {
			$str = get_class($e) . ($code ? ": [$code] ":': ') . $e->getMessage();
			
			# extra info, used by PDOException, ActionDBException, etc
			if(isset($e->errorInfo) && $e->errorInfo)
				$str .= "\n".trim(preg_replace('/[ \r\n\t]+/', ' ', $e->errorInfo))."\n";
			
			$str .= ' on line ' . $e->getLine() . ' in ' . $e->getFile();
		}
		
		if($includingTrace)
			$str .= "\n" . self::formatTrace($e, $html, $skip);
		
		# caused by...
		if($e instanceof ABException && $e->cause && is_object($e->cause) && $e->cause instanceof Exception) {
			if($html) {
				$str .= '<b>Caused by:</b><div style="margin-left:15px">'
					. self::format($e->cause, $includingTrace, $html, $skip)
					. '</div>';
			}
			else {
				# never include trace from caused php exception, because it is the same as it's parent.
				if($e->cause instanceof PHPException)
					$includingTrace = false;
				
				$str .= "\nCaused by:\n  " 
					. str_replace("\n", "\n  ", self::format($e->cause, $includingTrace, $html, $skip))."\n";
			}
		}
		
		if($html)
			$str .= '</div>';
		
		return $str;
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
	 * @return string
	 * @see    format()
	 */
	public static function formatTrace( Exception $e, $html = true, $skip = null )
	{
		$trace = $e->getTrace();
		$traceLen = count($trace);
		$str = '';
		
		if($e instanceof PHPException)
			$skip = is_array($skip) ? array_merge($skip, array('PHPException::rethrow')) : array('PHPException::rethrow');
		
		if($traceLen > 0)
		{
			if($html)
				$str .= "<div class=\"trace\"><pre>";
			
			if($skip) {
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
			
			$noSpace = strlen(strval($traceLen));
			
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
					$file = File::relativePath($ti['file'], @$_SERVER['DOCUMENT_ROOT']);
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