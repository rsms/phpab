<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitTestAssertion {
	
	/** @var int */
	public $lineNo = 0;
	
	/** @var string[] */
	public $lines = null;
	
	/** @var string */
	public $message = '';
	
	/** @var UnitTestCase */
	public $testCase = null;
	
	/**
	 * @param int
	 * @param string
	 */
	public function __construct($line, $message) {
		$this->lineNo = $line;
		$this->message = $message;
	}
	
	/**
	 * Get a line from the source file
	 *
	 * @param  int     Line number. If -1, $lineNo is set to the line on which the assertion occured.
	 * @param  bool    Apply syntax highlighting, using html, to the line.
	 * @return string  Returns null if the line could not be found or read.
	 */
	public function getLine($lineNo = -1, $html = true)
	{
		if($lineNo == -1)
			$lineNo = $this->lineNo;
		$lines = $this->getLines($lineNo, $lineNo, $html);
		return isset($lines[$lineNo]) ? $lines[$lineNo] : null;
	}
	
	/**
	 * Get a range of lines from the source file
	 *
	 * <b>Note:</b> The line reading might fail (without errors) for several reasons. 
	 * One might be thie assertion does not belong to a test case, in which the file 
	 * path is usually defined. Therefore, you are not guaranteed to get all requested
	 * lines. Also, if you request lines that does not exist in the file, this method
	 * will read as many as it can, and then return.
	 * I.e. <samp>$a->getLines(1,999999)</samp> will return lines 1-10 in a file with 
	 * a total of 10 lines.
	 *
	 * @param  int
	 * @param  int
	 * @param  bool   Apply syntax highlighting, using html, to the line.
	 * @return array  (int lineNo => string line) You must check if the requested 
	 *                lines accually was read, by calling isset($lines[123]).
	 */
	public function getLines($fromLine, $toLine, $html = true)
	{
		$lines = array();
		
		if($this->testCase && ($fp = fopen($this->testCase->getClassInfo()->getFileName(), 'r')))
		{
			$lineNo = 0;
			
			while(!feof($fp))
			{
				$lineStr = fgets($fp,4096);
				$lineNo++;
				
				if($lineNo >= $fromLine && $lineNo <= $toLine)
				{
					if($html) {
						$lines[$lineNo] = str_replace(
							array('&lt;?','?&gt;','&nbsp;','> '), 
							array('','',' ','>'), 
							highlight_string('<? '.trim($lineStr).' ?'.'>', true));
					}
					else {
						$lines[$lineNo] = trim($lineStr);
					}
					
					if($lineNo == $toLine)
						break;
				}
			}
			
			fclose($fp);
		}
		
		return $lines;
	}
	
	
	/** @return string */
	public function toString()
	{
		$lines = $this->getLines($this->lineNo-1, $this->lineNo+1, false);
		$str = '';
		
		if(isset($lines[$this->lineNo-1]))
			$str .= sprintf('   % -6d',$this->lineNo-1) . $lines[$this->lineNo-1] . "\n";
			
		$str .= '-> '. sprintf('% -6d',$this->lineNo). @$lines[$this->lineNo] ."\n";
		
		if(isset($lines[$this->lineNo+1]))
			$str .= sprintf('   % -6d',$this->lineNo+1) . $lines[$this->lineNo+1] . "\n";
		
		return rtrim($str,"\n");
	}
	
	
	/** @return string */
	public function toHTML()
	{
		$lines = $this->getLines($this->lineNo-1, $this->lineNo+1);
		
		$str = '';
		
		if(isset($lines[$this->lineNo-1]))
			$str .= '<div class="line"><code>'
				. str_replace(' ','&nbsp;',sprintf('% -6d',$this->lineNo-1))
				. preg_replace('/style="color:\s*[^;"]+/', 'style="', $lines[$this->lineNo-1])
				. '</code></div>';
			
		$str .= '<div class="line important"><code>'
			. str_replace(' ','&nbsp;',sprintf('% -6d',$this->lineNo)).'</code>'
			. @$lines[$this->lineNo]
			. '</div>';
		
		if(isset($lines[$this->lineNo+1]))
			$str .= '<div class="line"><code>'
				. str_replace(' ', '&nbsp;', sprintf('% -6d',$this->lineNo+1))
				. preg_replace('/style="color:\s*[^;"]+/', 'style="', $lines[$this->lineNo+1])
				. '</code></div>';
		
		return $str;
	}
}
?>