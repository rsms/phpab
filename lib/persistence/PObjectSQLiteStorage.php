<?
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