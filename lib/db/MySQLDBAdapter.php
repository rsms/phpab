<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage db
 */
class MySQLDBAdapter extends DBAdapter {
	
	/** @var mixed used for mysql resource after successful connection */
	protected $connection = array();
	
	/** @var string */
	public $adapter_name = 'MySQL';
	
	/**
	 * Map named types to native types
	 * @var array
	 */
	public $native_database_types = array(
		'primary_key' => 'int(11) unsigned NOT NULL auto_increment PRIMARY KEY',
		'string'      => 'varchar(255)',
		'text'        => 'text',
		'integer'     => 'int(11)',
		'float'       => 'float',
		'datetime'    => 'datetime',
		'timestamp'   => 'datetime',
		'time'        => 'datetime',
		'date'        => 'date',
		'binary'      => 'blob',
		'boolean'     => 'tinyint(1)'
	);
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 */
	public function __construct($host_or_socket, $database, $user = '', $password = '') {
		$this->connection = array($host_or_socket, $database, $user, $password);
	}
	
	/** @return void */
	public function __destruct() {
		if($this->connected)
			@mysql_close($this->connection);
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return array  An array of record hashes with the column names as a keys and 
	 *                fields as values. (string column => string value)
	 * @throws DBException
	 */
	public function selectAll($sql, $name = null) {
		return $this->select($sql, $name);
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return array  A record hash with column names as a keys and fields as values. 
	 *                (string column => string value)
	 * @throws DBException
	 */
	public function selectOne($sql, $name = null) {
		$result = $this->execute($sql, $name, true);
		if($row = mysql_fetch_assoc($result))
			return $row;
		return array();
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return DBColumn[] An enumerated array of column objects for the specified table
	 */
	public function columns($table_name, $name = null) {
		/*
		sql = "SHOW FIELDS FROM #{table_name}" 
		columns = []
		execute(sql, name).each { |field| columns << Column.new(field[0], field[4], field[1]) }
		columns
		*/
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return mixed  The last auto-generated ID from the affected table
	 * @throws DBException
	 */
	public function insert($sql, $name = null, $pk = null, $id_value = null) {
		$this->execute($sql, $name);
		return $id_value ? $id_value : mysql_insert_id($this->connection);
	}
	
	/**
	 * Executes the update statement
	 *
	 * @param  string
	 * @param  string
	 * @return int    Number of rows affected
	 * @throws DBException
	 */
	public function update($sql, $name = null) {
		$this->execute($sql, $name);
		return mysql_affected_rows($this->connection);
	}
	
	/**
	 * Executes SQL
	 *
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return mixed   resource result or void if $expecting_results == false
	 * @throws DBException
	 */
	public function execute($sql, $name = null, $expecting_results = false)
	{
		if(!$this->connected) $this->connect();
		
		try {
			if($expecting_results) {
				if(($res = mysql_query($sql, $this->connection)) === false)
					throw new DBException(mysql_error($this->connection), mysql_errno($this->connection), $sql);
				return $res;
			}
			else
				mysql_unbuffered_query($sql, $this->connection);
		}
		catch(PHPException $e) {
			$e->stripFunctionNames('mysql_unbuffered_query', 'mysql_query');
			$e = new DBException($e, $e->getMessage());
			$e->errorInfo = $sql;
			throw $e;
		}
	}
	
	/**
	 * Executes the delete statement
	 *
	 * @param  string
	 * @param  string
	 * @return int    Number of rows affected
	 * @throws DBException
	 */
	public function delete($sql, $name = null) {
		$this->execute($sql, $name);
		return mysql_affected_rows($this->connection);
	}
		
	/**
	 * Begins the transaction (and turns off auto-committing)
	 *
	 * @return void
	 */
	public function beginTransaction() {
		try {
			$this->execute('BEGIN');
		}
		catch(DBException $e) {
			# transactions are not supported
		}
	}
	
	/**
	 * Commits the transaction (and turns on auto-committing)
	 *
	 * @return void
	 */
	public function commitTransaction() {
		try {
			$this->execute('COMMIT');
		}
		catch(DBException $e) {
			# transactions are not supported
		}
	}
	
	/**
	 * Rolls back the transaction (and turns on auto-committing)
	 *
	 * @return void
	 */
	public function rollbackTransaction() {
		try {
			$this->execute('ROLLBACK');
		}
		catch(DBException $e) {
			# transactions are not supported
		}
	}
	
	/**
	 * Returns a string of the CREATE TABLE SQL statements for recreating 
	 * the entire structure of the database.
	 *
	 * @return string
	 * @throws DBException
	 */
	public function dumpStructure() {
		$structure = '';
		foreach($this->selectAll('SHOW TABLES') as $table) {
			$row = $this->selectOne('SHOW CREATE TABLE '.end($table[0]));
			$structure.= $row['Create Table'] . ";\n\n";
		}
		return $structure;
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws DBException
	 */
	public function deleteDatabase($name) {
		$this->execute('DROP DATABASE IF EXISTS '.$name);
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return array   records hash
	 */
	protected function select($sql, $name = null) {
		$result = $this->execute($sql, $name, true);
		$rows = array();
		while($row = mysql_fetch_assoc($result))
			$rows[] = $row;
		return $rows;
	}
		
	/**
	 * @return void
	 * @throws DBException
	 */
	protected function connect()
	{
		if($this->connected)
			return;
		
		try {
			$connection = mysql_connect($this->connection[0], $this->connection[2], $this->connection[3]);
		}
		catch(PHPException $e) {
			$e->stripFunctionNames('mysql_connect');
			throw new DBException($e, "Failed to connect to database '{$this->connection[1]}' "
				. "on '{$this->connection[0]}' with user '{$this->connection[2]}' using password "
				.($this->connection[3] ? 'YES' : 'NO'));
		}
		
		try {
			mysql_select_db($this->connection[1], $connection);
			$this->connection = $connection;
			$this->connected = true;
		}
		catch(PHPException $e) {
			$e->stripFunctionNames('mysql_select_db');
			throw new DBException($e, "Failed select database '{$this->connection[1]}'");
		}
	}
	
	/**
	 * @param  string  "hej's\\boll"
	 * @return string  "hej\'s\\boll"
	 */
	public function quoteString($string) {
		if($this->connected)
			return mysql_real_escape_string($string, $this->connection);
		else
			return mysql_escape_string($string);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function quoteColumnName($column) {
		return "`$column`";
	}
}
?>