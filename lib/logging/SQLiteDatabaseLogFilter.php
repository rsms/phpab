<?
/**
 * Intercept log messages and possibly save them to a SQLite database
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage logging
 */
class SQLiteDatabaseLogFilter extends DatabaseLogFilter {
	
	/**
	 * Available columns to be used for the <samp>db.columns</samp> parameter.
	 * @var array
	 */
	public static $availableColumns = array(
		'time'    => 'REAL',
		'level'   => 'INTEGER',
		'prefix'  => 'TEXT',
		'message' => 'TEXT',
		'cwd'     => 'TEXT');
	
	/** @var array */
	protected $parameters = array(
		'level' => Logger::LEVEL_FATAL,
		'db.file' => '',
		'db.table' => 'tLog',
		'db.columns' => array('time','level','prefix','message','cwd'),
	);
	
	/** @var SQLiteDatabase */
	public $db = null;
	
	private $insertSQL = '';
	
	/**
	 * Called when parameters has been set and
	 * the database connection (or such) needs to be reconfigured/setup.
	 *
	 * @return void
	 * @throws SQLiteException
	 * @throws IOException
	 */
	public function setupDatabase()
	{
		# first time?
		if(($create = !file_exists($this->parameters['db.file']))) {
			$dir = dirname($this->parameters['db.file']);
			if(!is_writable($dir))
				throw new IOException('Database file directory is not writable "'.$dir.'"');
		}
		
		$this->openDB();
		
		if($create)
			$this->createDB();
		
		$this->precompileSQL();
	}
	
	/** @ignore */
	public function __destruct() {
		$this->db = null;
	}
	
	/** @ignore */
	public function __wakeup() {
		$this->openDB();
	}
	
	
	/** @return void */
	private function openDB() {
		$err = null;
		if(!($this->db = new SQLiteDatabase($this->parameters['db.file'], 0666, $err)))
			throw new SQLiteException($err);
	}
	
	
	/** @return void */
	private function createDB()
	{
		# REF:  http://sqlite.org/lang_createtable.html
		$sql = 'CREATE TABLE '.$this->parameters['db.table']." (";
			
		foreach(self::$availableColumns as $col => $type)
			$sql .= "\t\nc".ucfirst($col) . ' ' . $type . ',';
		
		$sql = trim($sql,',') . ')';
		$this->run($sql);
	}
	
	
	/** @return void */
	private function precompileSQL() {
		$this->insertSQL = 'INSERT INTO ' . $this->parameters['db.table'] . ' (';
		
		foreach($this->parameters['db.columns'] as $c)
			$this->insertSQL .= 'c'.ucfirst($c).',';
		
		$this->insertSQL = trim($this->insertSQL,',') . ') VALUES (';
	}
	
	/**
	 * Called upon to store (INSERT) a log record into the database.
	 *
	 * Normally called upon by the <samp>filter</samp> method after
	 * figuring out a record should be logged.
	 *
	 * @param  LogRecord
	 * @param  string    A preformatted message, compiled by the <samp>filter</samp> 
	 *                   method, containing <samp>$record->getMessage()</samp> and/or 
	 *                   <samp>ABException::format($record->getThrown())</samp>
	 * @return void
	 * @throws SQLiteException
	 */
	public function insertRecord( LogRecord $rec, $formattedMessage )
	{
		# compile final sql
		$sql = $this->insertSQL;
		
		foreach($this->parameters['db.columns'] as $col)
		{
			if($col == 'time')
				$sql .= $rec->getTime();
			elseif($col == 'level')
				$sql .= $rec->getLevel();
			elseif($col == 'prefix')
				$sql .= "'".sqlite_escape_string($rec->getPrefix())."'";
			elseif($col == 'message')
				$sql .= "'".sqlite_escape_string($formattedMessage)."'";
			elseif($col == 'cwd')
				$sql .= "'".sqlite_escape_string(getcwd())."'";
			else
				continue;
			$sql .= ',';
		}
		
		$sql = trim($sql,',') . ');';
		
		$this->run($sql);
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws SQLiteException
	 */
	protected function run($sql) {
		#CDUtils::printError('SQLiteDatabaseLogFilter->run(): '.$sql."\n");
		if(!$this->db->queryExec($sql))
			throw new SQLiteException(sqlite_error_string($this->db->lastError()));
	}
}
?>
