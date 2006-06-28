<?
/**
 * Represents a file in a Subversion repository
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
class SVNFile extends File {
	
	/** @var SVNRepository */
	protected $rep = null;
	
	/** @var string */
	protected $path = '';
	
	/** @var SVNFile */
	public $parent = null;
	
	/** @var SVNFile[] */
	public $childs = array();
	
	/**
	 * @param string
	 * @param SVNRepository
	 */
	public function __construct($path, $repository = null)
	{
		$this->path = $path;
		#$this->rep = $repository;
	}
	
	/** @return string */
	public function getPath() {
		return $this->path;
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
	
	/** @return SVNFile or null if this is the repository root directory */
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
	 * @return string  contents
	 * @throws IOException
	 */
	public function getContents() {
		/*try {
			return file_get_contents($this->url->toString());
		}
		catch(PHPException $e) {
			$e->setMessage($e->getMessage() . ' ' . $this->url->toString());
			$e->rethrow('IOException', 'file_get_contents');
		}*/
		# TODO
	}
	
	/** @return string */
	public function toString() {
		return $this->path;
	}
}
?>