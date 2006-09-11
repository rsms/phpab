<?
/**
 * Date and Time representation and utility.
 *
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage util
 */
class Datetime {
	
	/** @var double Unix time with fractions */
	public $time = 0.0;
	
	
	/**
	 * @param  mixed  string datetime or int timestamp or float timestamp
	 * @param  mixed
	 * @todo   Recalculate time when timezone is specified
	 */
	public function __construct( $timestampOrString = -1, $timezone = null )
	{
		if(is_string($timestampOrString))
			$this->time = doubleval(strtotime($timestampOrString));
		elseif($timestampOrString != -1)
			$this->time = doubleval($timestampOrString);
		$this->time = microtime(1);
	}
	
	
	/** @return string  i.e. '+0200' */
	public function timeZoneOffset() {
		return strftime('%z', $this->time);
	}
	
	
	/** @return string  i.e. 'CEST' */
	public function timeZoneName() {
		return strftime('%Z', $this->time);
	}
	
	
	/**
	 * Return human readable difference betweed this datetime and $comparedToTime
	 * Uses to Datetime::formatAge()
	 *
	 * @param  double  timestamp
	 * @param  bool
	 * @param  bool
	 * @return string
	 */
	public function getFormattedDiff( $comparedToTime, $short = false, $complete = true ) {
		return self::formatAge(($comparedToTime > $this->time) ? ($comparedToTime - $this->time) : ($this->time - $comparedToTime), $short, $complete);
	}
	
	
	/** @ignore */
	public static function __test() {
		
		$now = microtime(1);
		$dt = new Datetime($now);
		assert($dt->time == $now);
		
		assert(self::formatAge(123.456789) == '');
	}
	
	
	/**
	 * @param  string  Format. See http://php.net/manual/en/function.strftime.php
	 * @return string
	 */
	public function toString( $format='%Y-%m-%d %H:%M:%S %Z' ) {
		return strftime($format, $this->time);
	}
	/** @ignore */
	public function __toString(){ return $this->toString(); }
	
	
	/**
	 * Return a human readable string expressing the number of seconds passed as the first argument.
	 * 
	 * @param  float  Seconds
	 * @param  bool   Short prefixes. ie "4 s" instead of "4 sec"
	 * @param  bool   Include full array of units. ie "2 days, 3 hours, 16 min, ..." instead of "2.1 days"
	 * @return string
	 */
	public static function formatAge( $seconds, $short = false, $complete = true )
	{
		$sec = $seconds;
		if($complete)
		{
			if($seconds == 0) return '0';
			
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
			if( $mi > 0 ) $ret .= "$mi us, "; # ISO: "\xb5"
			$ret .= "$ns ns";
			
			return ($seconds < 0) ? '-'.$ret : $ret;
		}
		else
		{
			if($sec <= 0)
				return '0';
			elseif($sec > 86400 && $sec < 129600)
				return round($sec / 86400,1).' day';
			elseif($sec > 86400)
				return round($sec / 86400,1).' days'; // 1,5... days
			elseif($sec > 3600)
				return round($sec / 3600,1).' h';
			elseif($sec > 60)
				return round($sec / 60,1).' min';
			elseif(intval($sec) > 0)
				return round($sec,2).' sec';
			elseif($sec > 0.001)
				return round($sec * 1000, 2).' ms';
			elseif($sec > 0.000001)
				return round($sec * 1000000, 2).' us';
			return intval($sec * 1000000000).' ns';
		}
	}
}
?>