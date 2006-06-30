<?
/**
 * Represents a directory in a Subversion repository
 * 
 * @version    $Id$
 * @author     Rasmus Andersson <http://hunch.se/>
 * @package    ab
 * @subpackage svn
 */
class SVNDirectory extends SVNFile {

	/** @return string */
	public function toString() {
		$str = $this->path . "\n";
		foreach($this->childs as $file)
			$str .= $file->toString() . "\n";
		return $str;
	}
	
	/** @return bool */
	public function isFile() {
		return false;
	}
	
	/** @return bool */
	public function isDir() {
		return true;
	}
	
	/**
	 * Always returns 0 for directories
	 *
	 * @return int (unsigned)
	 */
	public function size() {
		return 0;
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @param  int
	 * @param  SVNRepository
	 * @return SVNDirectory
	 */
	public static function fromData($path, $rev, $inode, $repository = null) {
		$f = new self($path, $repository);
		$f->rev = $rev;
		return $f;
	}
	
	/**
	 * Read data from file
	 *
	 * Will throw an IllegalOperationException, since you cant read contents of a directory.
	 *
	 * @return string  contents
	 * @throws IllegalOperationException
	 */
	public function getContents() {
		throw new IllegalOperationException('Can not read contents of a directory');
	}
}
?>