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
 * Represents a Subversion repository
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
class SVNRepository {

	/** @var int Youngest revision */
	protected $youngestRev = 0;
	
	/** @var string */
	protected $path = '';
	
	/** @var string */
	protected $uuid = '';
	
	/**
	 * @param string  Physical path to repository
	 */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/**
	 * Physical path
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * @return string
	 */
	public function getUUID() {
		if(!$this->uuid)
			$this->uuid = trim($this->look('uuid'));
		return $this->uuid;
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function look($command, $path = '', $arguments = '') {
		return SVN::look("$command $arguments '".$this->path."'".($path ? " '$path'" : ''));
	}
	
	/** @return string */
	public function getCacheKey($path = '', $revision = 0) {
		#return 'ab.svn.rep.'.md5($this->path);
		return 'svnrep'.str_replace('/','.',$this->path.$path).($revision ? '.r'.$revision : '');
	}
	
	/** @return int */
	public function youngestRev() {
		if(!$this->youngestRev)
			$this->youngestRev = intval(trim($this->look('youngest')));
		return $this->youngestRev;
	}
	
	/**
	 * Get a file
	 *
	 * The results of this method is  NOT cached.
	 * 
	 * @param  string
	 * @param  int      Specific revision of file
	 * @return SVNFile  Returns null if the file specified does not exist or if the path points to a directory
	 */
	public function getFile($path, $revision = 0)
	{
		# Query
		$line = trim($this->look('tree', $path, '--full-paths --show-ids'.($revision ? " -r $revision" : '')));
		
		if(!$line)
			return null;
		
		$rev = 0;
		$inode = 0;
		$line = self::parseTreeFileInfo($line, $rev, $inode);
		
		if(substr($line,-1) == '/')
			return null;
		
		return SVNFile::fromData($line, $rev, $inode, $this);
	}
	
	/**
	 * Get all files and directories in a directory for one level down
	 *
	 * The results of this method is cached using APC in memory.
	 * 
	 * @param  string
	 * @param  int           Show files for a specific revision. 0 = newest revision
	 * @param  bool
	 * @return SVNDirectory  Returns null if the directory specified does not exist
	 */
	public function getFiles($path = '/', $revision = 0, $noCache = false)
	{
		$path = rtrim($path, '/').'/';
		
		# Load mem cached data
		if(SVN::$apcEnabled && !$noCache) {
			$apcKey = $this->getCacheKey($path, $revision);
			if(($root = apc_fetch($apcKey)) !== false) {
				#print 'cache hit';
				return $root;
			}
		}
		
		
		# Query
		$tree = trim(SVN::look("tree --full-paths --show-ids".($revision ? " -r $revision" : '')." '".$this->path."' '$path'"
			. '|'.SVN::grepBin()." -E '^".str_replace(array('?','+'),array('\?','\+'),$path)."[^/]+/? *<'"));
		
		if(!$tree)
			return null;
		
		# Generate data
		$tree = explode("\n", $tree);
		$len = count($tree);
		$root = null;
		
		for($i=0; $i<$len; $i++)
		{
			$rev = 0;
			$inode = 0;
			$line = self::parseTreeFileInfo($tree[$i], $rev, $inode);
			
			if(!$root) {
				$root = SVNDirectory::fromData($line, $rev, $inode, $this);
			}
			elseif(substr($line,-1) == '/') {
				$root->appendChild(SVNDirectory::fromData($line, $rev, $inode));
			}
			else {
				$root->appendChild(SVNFile::fromData($line, $rev, $inode));
			}
		}
		
		
		# Save cache at request/response-session end
		if(SVN::$apcEnabled && !$noCache)
			SVN::scheduleCacheWrite($apcKey, $root);
		
		return $root;
	}
	
	/** @return string line */
	private static function parseTreeFileInfo($line, &$rev, &$inode)
	{
		$p = strpos($line, '<');
		$info = substr($line, $p);
		$line = substr($line, 0, $p-1);
		
		# parse quick info
		$p = strpos($info, '/');
		$inode = intval(substr($info, $p+1, -1));
		$x = strrpos($info, 'r');
		$rev = intval(substr($info, $x+1, $p-$x-1));
		return $line;
	}
	
}
?>