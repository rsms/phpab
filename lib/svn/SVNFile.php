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
 * Represents a file in a Subversion repository
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
class SVNFile extends File {
	
	/** @var string */
	protected $path = '';
	
	/** @var int */
	protected $rev = 0;
	
	/** @var int bytes */
	protected $size = -1;
	
	/** @var SVNRepository */
	protected $rep = null;
	
	/** @var string */
	protected $author = null;
	
	/** @var int timestamp */
	protected $modified = null;
	
	/** @var string */
	protected $revLog = null;
	
	
	/** @var SVNFile[] */
	public $childs = array();
	
	/** @var SVNFile */
	public $parent = null;
	
	/**
	 * @param string
	 * @param SVNRepository
	 */
	public function __construct($path, $repository = null)
	{
		$this->path = $path;
		$this->rep = $repository;
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @param  int
	 * @param  SVNRepository
	 * @return SVNFile
	 */
	public static function fromData($path, $rev, $inode, $repository = null) {
		$f = new self($path, $repository);
		$f->rev = $rev;
		return $f;
	}
	
	/** @return string */
	public function getPath() {
		return $this->path;
	}
	
	/** @return int */
	public function getRevision() {
		if($this->rev == 0) {
			$s = $this->rep->look('tree', $this->getPath(), '--show-ids');
			$p = strrpos($s, 'r')+1;
			$this->rev = intval(substr($s, $p, strrpos($s, '/')-$p));
		}
		return $this->rev;
	}
	
	/** @return string */
	public function getAuthor() {
		if($this->author === null)
			$this->loadInfo();
		return $this->author;
	}
	
	/**
	 * @return int Timestamp
	 */
	public function lastModified() {
		if($this->modified === null)
			$this->loadInfo();
		return $this->modified;
	}
	
	/** @return string Message */
	public function getRevisionLog($formatted = false) {
		if($this->revLog === null)
			$this->loadInfo();
		
		if($formatted) {
			return str_replace("\n", "<br />\n", $this->revLog);
		}
		
		return $this->revLog;
	}
	
	/** @return void */
	protected function loadInfo() {
		if(!$this->rep)
			throw new IllegalStateException('No repository associated');
		$info = explode("\n", trim($this->rep->look('info', $this->getPath(), $this->rev ? '-r '.$this->rev : '')));
		$this->author = array_shift($info);
		$this->modified = strtotime(substr(array_shift($info), 0, 25));
		array_shift($info);
		$this->revLog = trim(implode("\n", $info));
	}
	
	/**
	 * @return int (unsigned)
	 * @throws IOException
	 */
	public function size() {
		if($this->size == -1) {
			if($this->rep)
				$this->size = intval(SVN::look("cat '".$this->rep->getPath()."' '".$this->getPath()."'|wc -c"));
			else
				$this->size = 0;
		}
		return $this->size;
	}
	
	/**
	 * @param  SVNFile
	 * @return void
	 */
	public function appendChild(SVNFile $file) {
		$file->parent = $this;
		$file->rep = $this->rep;
		$this->childs[] = $file;
	}
	
	/** @return SVNFile or null if there are no children */
	public function firstChild() {
		return isset($this->childs[0]) ? $this->childs[0] : null;
	}
	
	/** @return SVNFile or null */
	public function getParent() {
		return $this->parent;
	}
	
	/** @return bool */
	public function isFile() {
		return true;
	}
	
	/** @return bool */
	public function isDir() {
		return false;
	}
	
	/** @return bool */
	public function exists() {
		return true;
	}
	
	/** @return void */
	public function deleteOnExit() {
	}
	
	/**
	 * Read data from file
	 *
	 * @param  int     Specific revision. 0 = youngest
	 * @param  bool    Disable memory-based cache
	 * @return string  contents
	 * @throws IOException
	 */
	public function getContents($revision = 0, $noCache = false)
	{
		if(!$this->rep)
			return null;
		
		if(SVN::$apcEnabled && !$noCache)
		{
			$cKey = $this->rep->getCacheKey($this->getPath().'-CONTENTS', $this->getRevision());
			
			# try load cache
			if(($dat = apc_fetch($cKey)) !== false) {
				#print 'cache hit';
				return $dat;
			}
			
			# load data
			$dat = $this->rep->look('cat', $this->getPath(), ($revision ? "-r $revision" : ''));
			
			# schedule cache write
			SVN::scheduleCacheWrite($cKey, $dat);
			
			return $dat;
		}
		else {
			return $this->rep->look('cat', $this->getPath(), ($revision ? "-r $revision" : ''));
		}
	}
	
	/** @return string */
	public function toString() {
		return $this->path;
	}
}
?>