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
 * Writes messages to stderr, stdout or stdlog
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
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
