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