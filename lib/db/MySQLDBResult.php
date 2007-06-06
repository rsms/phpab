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
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage db
 */
class MySQLDBResult extends DBResult {
	
	/** @var resource mysql result */
	protected $res = null;
	
	/**
	 * @param resource mysql result
	 */
	public function __construct($res) {
		$this->res = $res;
	}
	
	/**
	 * @param  int
	 * @return mixed
	 */
	public function fetchRow($style = DB_FETCH_ASSOC)
	{
		if($style == DB_FETCH_ASSOC)
			return mysql_fetch_assoc($this->res);
		elseif($style == DB_FETCH_NUM)
			return mysql_fetch_row($this->res);
		/*elseif($style == DB_FETCH_OBJ)
			return mysql_fetch_object($this->res);*/
		else
			return mysql_fetch_array($this->res, MYSQL_BOTH);
	}
	
	/**
	 * @return int  Returns -1 if the result has no row information
	 */
	public function rowCount() {
		if(($cols = mysql_num_rows($this->res)) === false)
			return -1;
		return $cols;
	}
	
	/**
	 * @return int  Returns -1 if the result has no column information
	 */
	public function columnCount() {
		if(($cols = mysql_num_fields($this->res)) === false)
			return -1;
		return $cols;
	}
	
}

?>