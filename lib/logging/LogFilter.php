<?
/**
 * Filter log messages
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage logging
 */
interface LogFilter {
	/**
	 * @param  array  array( mixed => mixed, ... )
	 * @return void
	 */
	public function setParameters( $parameters );
	
	/**
	 * @param  LogRecord
	 * @return bool  If false, the filter chain will break
	 * @throws Exception
	 */
	public function filter( LogRecord $rec );
}
?>
