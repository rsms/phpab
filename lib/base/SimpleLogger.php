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
	public $format = "[%date %time %level] %message";
	
	/**
	 * Add linebreak (ASCII 10) after each message.
	 * @var bool
	 */
	public $linebreak = true;
	
	/**
	 * If set to false, the string passed to the log methods are simply 
	 * written as-is, skipping formatting.
	 *
	 * @var bool
	 * @deprecated Not used anymore. Set format to a false value to disable it.
	 */
	public $enableFormatting;
	
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
	 * Print a FATAL message.
	 * Accepts (string msg) or (string format, mixed ...)
	 *
	 * @param  mixed
	 * @return void
	 */
	public function fatal(/* ... */) {
	  $this->log(func_get_args(), self::LEVEL_FATAL);
	}
	
	/**
	 * Print a ERROR message.
	 * Accepts (string msg) or (string format, mixed ...)
	 *
	 * @param  mixed
	 * @return void
	 */
	public function error(/* ... */) {
	  $this->log(func_get_args(), self::LEVEL_ERROR);
	}
	
	/**
	 * Print a WARN message.
	 * Accepts (string msg) or (string format, mixed ...)
	 *
	 * @param  mixed
	 * @return void
	 */
	public function warn(/* ... */) {
	  $this->log(func_get_args(), self::LEVEL_WARN);
	}
	
	/**
	 * Print a INFO message.
	 * Accepts (string msg) or (string format, mixed ...)
	 *
	 * @param  mixed
	 * @return void
	 */
	public function info(/* ... */) {
	  $this->log(func_get_args(), self::LEVEL_INFO);
	}
	
	/**
	 * Print a DEBUG message.
	 * Accepts (string msg) or (string format, mixed ...)
	 *
	 * @param  mixed
	 * @return void
	 */
	public function debug(/* ... */) {
	  $this->log(func_get_args(), self::LEVEL_DEBUG);
	}
	
	/**
	 * Forward a message to the current <samp>{@link LogHandler}</samp>
	 *
	 * If <samp>$msg</samp> is an <samp>object</samp>, it will be converted to a string 
	 * using $obj->__toString()</samp>. If <samp>$msg</samp> is an <samp>Exception</samp>,
	 * it will be converted to a string using <samp>ABException::format($e)</samp>. However,
	 * this is done by the LogHandler, so these are more or less guidelines than rules.
	 *
	 * @param  array
	 * @return void
	 */
	public function log( $args, $level ) {
		# don't log?
		if($level < $this->level)
			return;
		
		$msg;
		
		# vsprintf variadic?
  	if(count($args) > 1) {
			$fmt = array_shift($args);
			$msg = vsprintf($fmt, $args);
		} else {
			$msg = $args[0];
		}
		
		# format
		if($this->format) {
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
		if($this->linebreak) {
		  $msg .= "\n";
		}
		
		# publish
		fwrite($this->fd, $msg);
	}
}
?>