<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    hunch.ab
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
	
	public function start() {
		$this->reset();
		$this->rus(false);
	}
	
	public function stop($divideTimeBy = 1) {
		$this->rus(true);
		if($divideTimeBy > 1) {
			$this->utime /= $divideTimeBy;
			$this->stime /= $divideTimeBy;
			$this->rtime /= $divideTimeBy;
		}
		return $this->toString();
	}
	
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
			if( $mi > 0 ) $ret .= "$mi ".(self::$utf8 ? "\xc2" : 'u').'s, '; # ISO: "\xb5"
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
			
			if( ($sec > 0.000001 && $what == null) || $what == 'mi' || $what == "\xb5s" || $what == "\xc2s" || $what == 'us' )
				return round($sec * 1000000, 2).' '.(self::$utf8 ? "\xc2" : 'u').'s';
			
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