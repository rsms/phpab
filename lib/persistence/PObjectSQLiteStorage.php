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
 * @subpackage persistence
 */
class PObjectSQLiteStorage extends PObjectSQLStorage {
	
	/** @var resource */
	protected $db = null;
	
	/**
	 * Map php types to native DB types
	 * @var array
	 */
	public $nativeTypes = array(
		'boo' => 'INTEGER',
		'int' => 'INTEGER',
		'dou' => 'REAL',
		'str' => 'TEXT',
		'tex' => 'TEXT',
		'bin' => 'BLOB',
		'arr' => 'TEXT',
		'obj' => 'TEXT');
	
	/**
	 * @param string
	 */
	public function __construct($dbfile) {
		$this->db = @sqlite_open($dbfile, 0664, $err);
		if($err)
			throw new IOException('Failed to open database: '.$err);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	protected function dbquoteStr($string) {
		return sqlite_escape_string($string);
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws IOException
	 */
	public function dbexec($sql) {
		if(!@sqlite_exec($this->db, $sql, $err))
			$this->onStorageException($err);
	}
	
	/**
	 * @param  string
	 * @return array
	 * @throws IOException
	 */
	public function dbquery($sql) {
		if(($col = sqlite_array_query($this->db, $sql, SQLITE_ASSOC)) === false)
			$this->onStorageException(sqlite_error_string(sqlite_last_error($this->db)));
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	private function onStorageException($err) {
		$code = PObjectStorageException::UNKNOWN;
		if(stripos($err, 'no such table') !== false)
			$code = PObjectStorageException::NO_TABLE;
		throw new PObjectStorageException($err, $code);
	}
}
?>