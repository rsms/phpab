<?
/**
 * Represents a Subversion repository
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
class SVNRepository {
	
	
	
	/** @var string */
	protected $path = '';
	
	/**
	 * @param string  Physical path to repository
	 */
	public function __construct($path) {
		$this->path = $path;
	}
	
	protected function getFilesCacheKey() {
		return 'ab.svn.rep.'.md5($this->path);
	}
	
	/**
	 * @return SVNFile[]
	 */
	public function getFiles()
	{
		if(SVN::$apcEnabled) {
			$apcKey = $this->getFilesCacheKey();
			if(($root = apc_fetch($apcKey)) !== false)
				return $root;
		}
		
		$tree = explode("\n", SVN::look("tree '".$this->path."'"));
		$tempNode = new SVNDirectory('', $this);
		$fromIndex = 0;
		$this->getFilesWalker($tree, $tempNode, $fromIndex, count($tree));
		$root = $tempNode->firstChild();
		$root->parent = null;
		
		if(SVN::$apcEnabled)
		{
			$tmpfile = '/tmp/'.$apcKey.'.lock';
			
			if(!($fp = fopen($tmpfile,'w')))
				throw new IOException("Failed to create lock file '$tmpfile'");
			
			flock($fp, LOCK_EX);
			# check AGAIN after we get the lock.
			# If someone was faster than us
			if(apc_fetch($apcKey) !== false) {
				fclose($fp);
				return $root;
			}
			apc_store($apcKey, $root, SVN::$apcTTL);
			fclose($fp);
			@unlink($tmpfile);
		}
		return $root;
	}
	
	/**
	 * @return void
	 */
	private function getFilesWalker(&$tree, $parent, &$fromIndex, $toIndex, $entryLevel = 0)
	{
		$file = null;
		
		for(; $fromIndex < $toIndex; $fromIndex++)
		{
			$line =& $tree[$fromIndex];
			$level = strspn($line, ' ');
			
			if($level < $entryLevel)
			{
				$fromIndex--;
				break;
			}
			elseif($level == $entryLevel)
			{
				$slash = $parent->parent ? '/' : '';
				
				if(substr($line,-1) == '/')
					$file = new SVNDirectory($parent->getPath() . $slash . trim($line,' /'));
				else
					$file = new SVNFile($parent->getPath() . $slash . substr($line,$level));
				
				$parent->appendChild($file);
			}
			elseif($level > $entryLevel)
			{
				$this->getFilesWalker($tree, $file, $fromIndex, $toIndex, $level);
			}
		}
	}
}
?>