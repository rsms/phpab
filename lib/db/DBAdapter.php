<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage db
 */
abstract class DBAdapter {
	
	/** @var bool */
	public $connected = false;
	
	/**
	 * Human-readable name of the adapter.
	 * Use mixed case - one can always use downcase if needed.
	 *
	 * @var string
	 */
	public $adapter_name = 'Abstract';
	
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
	 * @return array  An array of record hashes with the column names as a keys and 
	 *                fields as values. (string column => string value)
	 * @throws ActionDBException
	 */
	abstract public function selectAll($sql, $name = null);
	
	/**
	 * @param  string
	 * @param  string
	 * @return array  A record hash with column names as a keys and fields as values. 
	 *                (string column => string value)
	 * @throws ActionDBException
	 */
	abstract public function selectOne($sql, $name = null);
	
	/**
	 * @param  string
	 * @param  string
	 * @return ActionDBColumn[] An enumerated array of column objects for the specified table
	 */
	abstract public function columns($table_name, $name = null);
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return mixed  The last auto-generated ID from the affected table
	 * @throws ActionDBException
	 */
	abstract public function insert($sql, $name = null, $pk = null, $id_value = null);
	
	/**
	 * Executes the update statement
	 *
	 * @param  string
	 * @param  string
	 * @return int    Number of rows affected
	 * @throws ActionDBException
	 */
	abstract public function update($sql, $name = null);
	
	/**
	 * Executes SQL
	 *
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return mixed   resource result or void if $expecting_results == false
	 * @throws ActionDBException
	 */
	abstract public function execute($sql, $name = null, $expecting_results = false);
	
	/**
	 * Executes the delete statement
	 *
	 * @param  string
	 * @param  string
	 * @return int    Number of rows affected
	 * @throws ActionDBException
	 */
	abstract public function delete($sql, $name = null);
		
	/**
	 * Begins the transaction (and turns off auto-committing)
	 *
	 * @return void
	 * @throws ActionDBException
	 */
	abstract public function beginTransaction();
	
	/**
	 * Commits the transaction (and turns on auto-committing) 
	 *
	 * @return void
	 * @throws ActionDBException
	 */
	abstract public function commitTransaction();
	
	/**
	 * Rolls back the transaction (and turns on auto-committing)
	 *
	 * @return void
	 * @throws ActionDBException
	 */
	abstract public function rollbackTransaction();
	
	/**
	 * Returns a string of the CREATE TABLE SQL statements for recreating 
	 * the entire structure of the database.
	 *
	 * @return string
	 * @throws ActionDBException
	 */
	abstract public function dumpStructure();
	
	
	# ------- end of abstract methods --------
	
	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function addLimit(&$sql, $limit) {
		$sql .= " LIMIT {$limit}";
	}
	
	/**
	 * @param  mixed
	 * @param  DBColumn or null
	 * @return string
	 * @throws IllegalArgumentException  if a value of type resource or unknown is passed
	 */
	public function quote($value, $column = null)
	{
		switch(gettype($value)) {
			case 'string':
				if($column && $column->type == T_BINARY)
					return "'". self::quoteString($column->stringToBinary($value)) ."'";
				else
					return "'". self::quoteString($value) ."'";
			
			case 'integer':
				if($column && $column->type == T_DATE)
					return "'". date('Y-m-d H:i:s', $value) ."'";
			case 'double':
				return $value;
			
			case 'boolean':
				if($value)
					return ($column && $column->type == T_BOOL) ? "'t'" : '1';
				else
					return ($column && $column->type == T_BOOL) ? "'f'" : '0';
			
			case 'NULL':
				return 'NULL';
			
			case 'array':
			case 'object':
				return "'". self::quoteString(serialize($value)) ."'";
			
			default:
				throw new IllegalArgumentException("Unsupported data type '".gettype($value).($column ? " for column '{$column->name}'":''));
		}
	}
	
	/**
	 * @param  string  "hej's\\boll"
	 * @return string  "hej\'s\\boll"
	 */
	public function quoteString($string) {
		return addslashes($string);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function quoteColumnName($column) {
		return $column;
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	/*public function initializeSchemaInformation() {
		try {
			$this->execute("CREATE TABLE schema_info (version {$this->native_database_types['integer']})");
			$this->insert('INSERT INTO schema_info (version) VALUES(0)');
		} catch(ActionDBException $e) {
			# Schema has allready been intialized
		}
	}*/
	
	/**
	 * @param  string
	 * @return void
	 * @throws ActionDBException
	 */
	public function recreateDatabase($name) {
		$this->deleteDatabase($name);
		$this->createDatabase($name);
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws ActionDBException
	 */
	public function deleteDatabase($name) {
		$this->execute('DROP DATABASE '.$name);
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws ActionDBException
	 */
	public function createDatabase($name) {
		$this->execute('CREATE DATABASE '.$name);
	}
	
	/**
	 * Create a table in this database
	 *
	 * @param  string  Table name
	 * @param  array   Column definitions {string column_name => {string 'type' => string named_type, string 'options' => {...}}, ...}
	 * @return void
	 */
	public function createTable($name, $columns = array())
	{
		$this->execute("CREATE TABLE {$name} (id {$this->native_database_types['primary_key']})");
		foreach($column as $column_name => $params)
			$this->addColumn($name, $column_name, $params['type'], $params['options']);
	}
	
	/**
	 * Delete a table from this database (DROP TABLE)
	 *
	 * @param  string  Table name
	 * @return void
	 */
	public function deleteTable($name) {
		$this->execute('DROP TABLE '.$name);
	}
	
	/**
	 * Add a column to a table in this database
	 *
	 * <b>Available options for <samp>$options</samp>:</b>
	 *   - <i>limit:</i>    Limiting the length, or specifying enums. (ie. 255 or "'t','f'")
	 *   - <i>unsigned:</i> Boolean value, only valid if $type == 'integer'. Defaults to false.
	 *   - <i>null:</i>     Boolean value. If false, NOT NULL will be used in description, forcing a non-null 
	 *                      value for inserts. Defaults to true.
	 *   - <i>default:</i>  Default value to be used on inserts w/o a value for this column. (ie. "Untitled")
	 *
	 * @param  string
	 * @param  string
	 * @param  string  Wich must be one of the keys in $this->native_database_types
	 * @param  array  
	 */
	public function addColumn($table_name, $column_name, $type, $options = array())
	{
		$sql = "ALTER TABLE $table_name ADD $column_name ".$this->native_database_types[$type];
		
		if(isset($options['limit']))
			$sql.= '('.$options['limit'].')';
		
		if(isset($options['unsigned']) && $options['unsigned'] && $type == 'integer')
			$sql.= ' unsigned';
		
		if(isset($options['null']) && !$options['null'])
			$sql.= ' NOT NULL';
		
		if(isset($options['default']))
			$sql.= " DEFAULT '{$options['default']}'";
		
		$this->execute($sql, 'Add column');
	}
	
	/**
	 * Delete a column from a table
	 *
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function deleteColumn($table_name, $column_name) {
		$this->execute("ALTER TABLE $table_name DROP ".$this->quoteColumnName($column_name), 'Delete column');
	}
		
}
?>