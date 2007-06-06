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