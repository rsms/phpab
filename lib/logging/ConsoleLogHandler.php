<?
/**
 * Writes messages to stderr, stdout or stdlog
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage logging
 */
class ConsoleLogHandler implements LogHandler
{
	private $pipe = 'stderr';
	private $fd = false;
	
	/**
	 * @param  string
	 */
	public function __construct( $pipe = 'stderr' ) {
		$this->pipe = $pipe;
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @return void
	 * @throws IOException
	 */
	public function publish( LogRecord $rec )
	{	
		if(!$this->fd) {
			if(($this->fd = fopen('php://'.$this->pipe, 'w')) === false)
				throw new IOException('Failed to open output stream on php://'.$this->pipe.' for writing');
		}
		
		if(fwrite($this->fd, $rec->toString()."\n") === false)
			throw new IOException('Failed to log message');
		
		# Maybe this is overkill...
		@fflush($this->fd);
	}
	
	/**
	 * @return string[]
	 * @ignore
	 */
	public function __sleep() {
		return array('pipe');
	}
	
	/**
	 * @return void
	 * @ignore
	 */
	public function __destruct() {
		@fclose($this->fd);
	}
}
?>