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
define('DB_FETCH_ASSOC', 0);
define('DB_FETCH_NUM', 1);
define('DB_FETCH_BOTH', 2);

/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage db
 */
abstract class DBResult {
	
	/**
	 * @param  int
	 * @return mixed
	 */
	abstract public function fetchRow($style = DB_FETCH_ASSOC);
	
	/**
	 * @return array
	 */
	public function fetchAll($style = DB_FETCH_ASSOC) {
		$rows = array();
		while($row = $this->fetchRow($style))
			$rows[] =& $row;
		return $rows;
	}
	
	/**
	 * @return int  Returns -1 if the result has no row information
	 */
	abstract public function rowCount();
	
	/**
	 * @return int  Returns -1 if the result has no column information
	 */
	abstract public function columnCount();
	
}
?>