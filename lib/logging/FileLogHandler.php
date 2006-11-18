<?
/**
 * Appends messages to a file
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage logging
 */
class FileLogHandler implements LogHandler
{
	private $file;
	
	/**
	 * @param  string
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}
	
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setFile( $path ) {
		$this->file = $path;
	}
	
	
	/**
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}
	
	
	/**
	 * @param  string
	 * @param  int
	 * @return void
	 */
	public function publish( LogRecord $rec )
	{	
		if(@error_log($rec->toString(), 3, $this->file) === false)
			throw new IOException('Failed to log message');
	}
	
	
	/**
	 * @return string[]
	 */
	public function __sleep() {
		return array('file');
	}
	
	
	/**
	 * Rotates log file, if needed (and enabled in cd config).
	 * 
	 * @return void
	 */
	public function __destruct() {
		$this->rotate();
	}
	
	
	/**
	 * Rotate
	 */
	private function rotate()
	{
		$limit = intval(cdCtx('log.rotate.limit', 0));
		if($limit < 1)
			return;
		
		if(!file_exists($this->file))
			return;
		
		if(filesize($this->file) < $limit)
			return;
		
		$pat = cdCtx('log.rotate.datepattern', 'ymd');
		$nameDate = $this->file . '.' . date($pat);
		$saveAs = $nameDate;
		
		$i = 1;
		while(file_exists($saveAs))
			$saveAs = $nameDate . '.' . ($i++);
		
		CDUtils::mv($this->file, $saveAs);
	}
}
?>
