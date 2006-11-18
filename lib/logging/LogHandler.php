<?
/**
 * A Handler object takes log messages from a Logger and exports them.
 * 
 * It might for example, write them to a console or write them to a file, or send 
 * them to a network logging service, or forward them to an OS log, or whatever.
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage logging
 */
interface LogHandler {
	/**
	 * @param  LogRecord
	 * @return void
	 * @throws IOException
	 */
	public function publish( LogRecord $rec );
}
?>
