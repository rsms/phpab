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
