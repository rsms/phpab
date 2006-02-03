<?
/**
 * Intercept log messages and possibly save them to a database
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage logging
 */
abstract class DatabaseLogFilter implements LogFilter {
	
	protected $parameters = array(
		'level' => Logger::LEVEL_FATAL
	);
	
	/**
	 * @param  array  array( mixed => mixed, ... )
	 * @return void
	 */
	public function setParameters( $parameters ) {
		if(is_array($parameters))
			$this->parameters = $parameters;
		$this->setupDatabase();
	}
	
	/**
	 * Called when parameters has been set and
	 * the database connection (or such) needs to be reconfigured/setup.
	 *
	 * <b>Important:</b> Make sure you implement the special 
	 * <samp>__wakeup()</samp> method to re-open your database. Since
	 * log filters are exchanged using serialization between different 
	 * job processes, this is a necessity. Example:
	 * <code>
	 * public function __wakeup() {
	 *   $this->openDB();
	 * }
	 * </code>
	 *
	 * @return void
	 * @throws Exception
	 */
	public abstract function setupDatabase();//{}
	
	/**
	 * Called upon to store (INSERT) a log record into the database.
	 *
	 * Normally called upon by the <samp>filter</samp> method after
	 * figuring out a record should be logged.
	 *
	 * @param  LogRecord
	 * @param  string    A preformatted message, compiled by the <samp>filter</samp> 
	 *                   method, containing <samp>$record->getMessage()</samp> and/or 
	 *                   <samp>MException::format($record->getThrown())</samp>
	 * @return void
	 * @throws Exception
	 */
	public abstract function insertRecord( LogRecord $record, $formattedMessage );//{}
	
	/**
	 * @param  LogRecord
	 * @return bool  If false, the filter chain will break
	 * @throws Exception
	 */
	public function filter( LogRecord $rec )
	{	
		// dont't filter?
		if(($rec->getLevel() < $this->parameters['level']) || cdCtx('cli_debug'))
			return true;
		
		// Message
		$msg = '';
		if($rec->getMessage())
			$msg .= $rec->getMessage()."\n";
		if($rec->getThrown())
			$msg .= MException::format($rec->getThrown(), true, false);
		$msg = trim($msg);
		
		// forward
		$this->insertRecord($rec, $msg);
		
		return true;
	}
}
?>