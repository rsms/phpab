<?
/**
 * Sends email on for specified log levels.
 * 
 * <b>Parameters</b><br/>
 * <table><tr><th>key</th><th>type</th><th>default value</th></tr>
 *   <tr><td>level</td>  <td>int</td>   <td><samp>Logger::LEVEL_FATAL</samp></td></tr>
 *   <tr><td>to</td>     <td>string</td><td><samp>noone@localhost</samp></td></tr>
 *   <tr><td>from</td>   <td>string</td><td><samp>contentd@localhost</samp></td></tr>
 *   <tr><td>subject</td><td>string</td><td><samp>[CONTENTD] %s in %s</samp></td></tr>
 * </table>
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage logging
 */
class MailLogFilter implements LogFilter
{
	private $conf = array(
		'level' => Logger::LEVEL_FATAL,
		'to' => 'noone@localhost',
		'from' => 'contentd@localhost',
		'subject' => '%e in %j'
	);
	
	/**
	 * @param  array  array( mixed => mixed, ... )
	 * @return void
	 */
	public function setParameters( $parameters ) {
		if(is_array($parameters)) {
			if(isset($parameters['level']))
				$parameters['level'] = intval($parameters['level']);
			$this->conf = array_merge($this->conf, $parameters);
		}
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @return bool  If the message should be passed on to the next filter or to the log handler.
	 * @throws Exception
	 */
	public function filter( LogRecord $rec ) {
		
		// dont't filter?
		if(($rec->getLevel() < $this->conf['level']) || cdCtx('cli_debug'))
			return true;
		
		// get group
		$group = '?';
		if(($id = @posix_getegid()) !== false)
			if(($id = @posix_getgrgid($id)) !== false)
				$group = $id['name'];
		
		// Date & Level
		$msg= 'Time:       ' . $rec->getTimeFormat() . "\n"
			. 'Group:User: ' . $group . ':' . cdUser() . "\n"
			. 'CWD:        ' . getcwd() . "\n"
			. 'Level:      ' . $rec->getLevelName() . "\n";
		
		// Prefix
		$prefix = $rec->getPrefix();
		if($prefix)
			$msg .= "Log Prefix: $prefix\n";
		else
			$prefix = 'main';
		$msg .= "\n";
		
		// Message
		if($rec->getThrown())
			$msg .= MException::format($rec->getThrown(), true, false);
		if($rec->getMessage())
			$msg .= $rec->getMessage();
		
		// Email headers
		$headers = 'From: '. $this->conf['from'] . "\r\nX-Mailer: contentd/" . cdVersion();
		
		// Subject
		// %e = exception name, %l = log level, %j = job name
		$logLevelName = ucfirst(trim(strtolower($rec->getLevelName())));
		$subject = strtr($this->conf['subject'], array(
			'%e' => ($rec->getThrown() ? get_class($rec->getThrown()) : $logLevelName),
			'%l' => $logLevelName,
			'%j' => $prefix));
		
		// Action
		if(!mail($this->conf['to'], $subject, $msg, $headers)) {
			$rec->setMessage($rec->getMessage() 
				. '. Additionaly, the Mail log filter failed to mail "'
				. $this->conf['to'] . '"');
		}
		
		return true;
	}
}
?>