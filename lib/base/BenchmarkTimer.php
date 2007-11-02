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
 * Measure code execution
 *
 * <b>Example:</b><code>
 * $timer = new BenchmarkTimer();
 * for($i=0;$i<10000;$i++)
 *     $dummy = 4337*(1337/89024)*23;
 * print $timer->stop();
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage util
 */
class BenchmarkTimer {
	
	/** @var bool */
	public static $utf8 = true;
	
	/** @ignore */
	public static $HAVE_RUSAGE = 1;
	
	private $buff = array(0,0,0,0);
	private $utime = 0.0;
	private $stime = 0.0;
	private $rtime = 0.0;
	
	/**
	 * @param bool
	 */
	public function __construct($startImmediately = true) {
		if($startImmediately)
			$this->start();
	}
	
	/**
	 * Start the timer
	 *
	 * @return void
	 */
	public function start() {
		$this->reset();
		$this->rus(false);
	}
	
	/**
	 * Stop the timer
	 *
	 * @return string  Returns the value of $timer->toString()
	 */
	public function stop($divideTimeBy = 1) {
		$this->rus(true);
		if($divideTimeBy > 1) {
			$this->utime /= $divideTimeBy;
			$this->stime /= $divideTimeBy;
			$this->rtime /= $divideTimeBy;
		}
		return $this->toString();
	}
	
	/**
	 * Reset the timer
	 *
	 * @return void
	 */
	public function reset() {
		$this->utime = 0.0;
		$this->stime = 0.0;
		$this->rtime = 0.0;
	}
	
	/**
	 * @param  bool
	 * @return mixed If $asString = false, double seconds is returned
	 */
	public function getUserTime( $asString = false ) {
		return $asString ? self::format($this->utime/1000000) : $this->utime/1000000;
	}
	
	/**
	 * @param  bool
	 * @return mixed If $asString = false, double seconds is returned
	 */
	public function getSystemTime( $asString = false ) {
		return $asString ? self::format($this->stime/1000000) : $this->stime/1000000;
	}
	
	/**
	 * @param  bool
	 * @return mixed If $asString = false, double seconds is returned
	 */
	public function getRealTime( $asString = false ) {
		return $asString ? self::format($this->rtime) : $this->rtime;
	}
	
	/**
	 * Return formatted time consumed
	 *
	 * Format: <samp>User: TIME, System: TIME, Real: TIME</samp>
	 *
	 * @return string
	 */
	public function toString() {
		return 'User: ' . self::format($this->utime/1000000, false)
			. ', System: ' . self::format($this->stime/1000000, false)
			. ', Real: ' . self::format($this->rtime, false);
	}
	
	/** @return string */
	public function __toString() { return $this->toString(); }
	
	/**
	 * @param  float  seconds
	 * @param  bool
	 * @param  string
	 * @return string
	 */
	public static function format( $sec, $complete = true, $what = null )
	{
		if($complete)
		{
			if($sec <= 0) return '0';
			
			$days = intval( $sec / 86400);
			$sec -= $days * 86400;
			
			$hours = intval( $sec / 3600);
			$sec -= $hours * 3600;
			
			$mins = intval( $sec / 60);
			$sec -= $mins * 60;
			
			$secs = intval($sec);
			$sec -= $secs;
			
			$ms = intval($sec * 1000);
			$sec -= $ms / 1000;
			
			$mi = intval($sec * 1000000);
			$sec -= $mi / 1000000;
			
			$ns = intval($sec * 1000000000);
			
			$ret = '';
			if( $days > 1 ) $ret .= "$days days, ";
			elseif( $days == 1 ) $ret .= "1 day, ";
			if( $hours > 0 ) $ret .= "$hours h, ";
			if( $mins > 0 ) $ret .= "$mins min, ";
			if( $secs > 0 ) $ret .= "$secs sec, ";
			if( $ms > 0 ) $ret .= "$ms ms, ";
			if( $mi > 0 ) $ret .= "$mi ".(self::$utf8 ? "\xc2\xb5" : 'u').'s, '; # ISO/Latin-1: "\xb5"
			$ret .= "$ns ns";
			
			return $ret;
		}
		else
		{
			if($sec <= 0) return '0';
			
			if( ($sec > 86400 && $sec < 129600 && $what == null) || ($what == 'd' && $sec < 129600) )
				return round($sec / 86400,1).' day';
			
			if( ($sec > 86400 && $what == null) || $what == 'd' )
				return round($sec / 86400,1).' days'; // 1,5... days
			
			if( ($sec > 3600 && $what == null) || $what == 'h' )
				return round($sec / 3600,1).' h';
			
			if( ($sec > 60 && $what == null) || $what == 'm' )
				return round($sec / 60,1).' min';
			
			if( (intval($sec) > 0 && $what == null) || $what == 's' )
				return round($sec,2).' sec';
			
			if( ($sec > 0.001 && $what == null) || $what == 'ms' )
				return round($sec * 1000, 2).' ms';
			
			if( ($sec > 0.000001 && $what == null) || $what == 'mi' || $what == 'mu' || $what == "\xb5s" || $what == "\xc2s" || $what == 'us' )
				return round($sec * 1000000, 2).' '.(self::$utf8 ? "\xc2\xb5" : 'u').'s';
			
			return intval($sec * 1000000000).' ns';
		}
	}
	
	private function rus($end) {
		if(self::$HAVE_RUSAGE) {
			$this->buff = getrusage();
			if($end) {
				$this->rtime = microtime(true) - $this->rtime;
				$this->utime = $this->buff["ru_utime.tv_sec"].$this->buff["ru_utime.tv_usec"] - $this->utime;
				$this->stime = $this->buff["ru_stime.tv_sec"].$this->buff["ru_stime.tv_usec"] - $this->stime;
			}
			else {
				$this->rtime = microtime(true);
				$this->utime = $this->buff["ru_utime.tv_sec"].$this->buff["ru_utime.tv_usec"];
				$this->stime = $this->buff["ru_stime.tv_sec"].$this->buff["ru_stime.tv_usec"];
			}
		}
		else {
			if($end) $this->rtime = microtime(true) - $this->rtime;
			else     $this->rtime = microtime(true);
		}
	}
}
BenchmarkTimer::$HAVE_RUSAGE = function_exists('getrusage');
?>