<?
class PObjectStorage {
	
	# TODO: temporary
	public static $db = null;
	
	/**
	 * Map php types to native DB types
	 * @var array
	 */
	public static $nativeTypes = array(
		'boo' => 'INTEGER',
		'int' => 'INTEGER',
		'dou' => 'REAL',
		'str' => 'TEXT',
		'tex' => 'TEXT',
		'bin' => 'BLOB',
		'arr' => 'TEXT',
		'obj' => 'TEXT');
	
	/** @var PObject */
	protected static $refObjs = array();
	
	/** @var string[] */
	protected static $phpTypes = array('str','int','dou','boo','NUL','arr','obj');
	
	
	/**
	 * @param  string
	 * @return PObject  uninitialized object
	 */
	protected static function referenceObj($class)
	{
		if(!isset(self::$refObjs[$class]))
			self::$refObjs[$class] = PHP::classcast(new stdClass, $class);
		return self::$refObjs[$class];
	}
	
	/**
	 * If the storage backend is a database, this specified which table name 
	 * should be used to store objects. If not defined, the Inflator will 
	 * generate a plural, lower case variant of the class name.
	 *
	 * @param  string
	 * @return string
	 */
	public static function storageClass($class) {
		if(!isset(self::storageClasses[$class]))
			self::storageClasses[$class] = Inflector::tableize($class);
		return self::storageClasses[$class];
	}
	
	
	/**
	 * @param  mixed  string Classname or PObject instance
	 * @param  mixed
	 * @return PObject or null if not found
	 */
	public function find($classOrObj, $id)
	{
		if(is_string($classOrObj)) {
			$class = $classOrObj;
			$classOrObj = self::referenceObj($class);
		}
		else
			$class = get_class($classOrObj);
		
		$pk =& $classOrObj->primaryKey;
		
		if($properties = self::$db->arrayQuery(self::fsql("SELECT * FROM %s WHERE %s = '%s'", $classOrObj->storageClass(), $pk, $classOrObj->$pk), SQLITE_ASSOC))
			return new $class($properties);
		
		return null;
	}
	
	
	/**
	 * @param  PObject
	 * @param  string  Primary key
	 * @return void
	 */
	public function save(PObject $obj, $pk)
	{
		if(!$pk)
		{
			$keys = array();
			$values = array();
			foreach($obj->getProperties(false) as $k => $v) {
				$keys[] = $k;
				$values[] = self::dbquote($v);
			}
			self::dbexec('INSERT INTO '.$obj->storageClass().' ('.implode(',', $keys).') VALUES ('.implode(',', $values).')');
		}
		else {
			$sql = 'UPDATE '.$this->storageClass().' SET ';
			
			foreach($obj->getProperties(false) as $k => $v)
				$sql .= $k . '=' . self::dbquote($v) . ',';
			
			$sql = rtrim($sql, ','). " WHERE $pk='".$obj->$pk."'";
			self::dbexec($sql);
		}
	}
	
	
	/**
	 * @param  PObject
	 * @param  string  Primary key
	 * @return void
	 * @throws IllegalOperationException if object is not yet persistent
	 */
	public function delete(PObject $obj, $pk)
	{
		if(!$obj->$pk)
			throw new IllegalOperationException('Object is not persistent');
		self::dbexec(self::fsql("DELETE FROM %s WHERE %s = '%s'", $obj->__table(), $pk, $obj->$pk));
	}
}
?>