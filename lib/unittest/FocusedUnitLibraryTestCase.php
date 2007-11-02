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
 * Perform tests on certain classes and/or files in a library.
 *
 * @version    $Id: UnitLibraryTestCase.php 179 2007-06-06 21:20:00Z rasmus $
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class FocusedUnitLibraryTestCase extends UnitLibraryTestCase
{
  /** @var array */
  protected $filename_filters = array();
  
  /** @var array */
  protected $classname_filters = array();
  
	/**
	 * @param string
	 * @param bool
	 */
	public function __construct($path, $filename_filters=array(), $classname_filters=array(), $recursive=true) {
    parent::__construct($path, $recursive);
    if($filename_filters) {
      if(!is_array($filename_filters)) {
        throw new IllegalTypeException('$filename_filters must be an array, not '.gettype($filename_filters));
      }
      $this->filename_filters = $filename_filters;
    }
    if($classname_filters) {
      if(!is_array($classname_filters)) {
        throw new IllegalTypeException('$classname_filters must be an array, not '.gettype($classname_filters));
      }
      $this->classname_filters = $classname_filters;
    }
	}
	
	/**
	 * Meant for overriding. This implementation always returns true.
	 * 
	 * @param  ABReflectionClass
	 * @return bool
	 */
	protected function shouldTestClass(ABReflectionClass $classInfo) {
	  foreach($this->filename_filters as $pattern) {
	    $this->log->debug("FocusedUnitLibraryTestCase->shouldTestClass: filename %s == %s\n",
	      $pattern, $classInfo->getFileName());
	    if(fnmatch($pattern, $classInfo->getFileName())) {
	      $this->log->debug("FocusedUnitLibraryTestCase->shouldTestClass: filename Match!\n");
	      return true;
	    }
	  }
	  foreach($this->classname_filters as $pattern) {
	    $this->log->debug("FocusedUnitLibraryTestCase->shouldTestClass: classname %s == %s\n",
	      $pattern, $classInfo->getName());
	    if(fnmatch($pattern, $classInfo->getName())) {
	      $this->log->debug("FocusedUnitLibraryTestCase->shouldTestClass: classname Match!\n");
	      return true;
	    }
	  }
	  return false;
	}
}
?>