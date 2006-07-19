<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 */
class PObject {
	
	/** @var PObjectStorage */
	public static $storage = null;
	
	/** @var string */
	protected static $primaryKey = 'id';
	
	//---------------------------------------------
	/** @var ReflectionClass */
	private $reflectionCache = null;
	
	//---------------------------------------------
	/** @var int */
	public $id = 0;
	
	
	/**
	 * @param  array Associative array with initial properties
	 */
	public function __construct($properties = array()) {
		foreach(array_keys($properties) as $k)
			$this->$k =& $properties[$k];
	}
	
	/**
	 * @param  string
	 * @param  mixed
	 * @return PObject
	 */
	public static function find($class, $id) {
		return self::$storage->find($class, $id);
	}
	
	
	/**
	 * @return void
	 */
	public function save() {
		self::$storage->save($this, self::$primaryKey);
	}
	
	
	/**
	 * @return void
	 */
	public function delete() {
		self::$storage->delete($this, self::$primaryKey);
	}
	
	
	/**
	 * Return an associative array of all storable object properties, 
	 * with references to the actual object property values.
	 * 
	 * @return array
	 */
	public function &getProperties($includePrimaryKey = false)
	{
		$pk =& $this->primaryKey;
		$props = array();
		
		foreach($this->__reflection()->getProperties() as $prop) {
			if($prop->isPublic() && !$prop->isStatic()) {
				$name = $prop->getName();
				if(!$includePrimaryKey && $name == $pk)
					continue;
				$props[$name] =& $this->$name;
			}
		}
		
		return $props;
	}
	
	/**
	 * @param  string Property name
	 * @return string Native storage type
	 */
	public function getNativePropertyType($property)
	{
		$type = null;
		return $this->nativePropertyType($this->__reflection()->getProperty($property), $this->__reflection()->getDefaultProperties(), $type);
	}
	
	/**
	 * @param  ReflectionProperty
	 * @param  string
	 * @return string
	 */
	protected function nativePropertyType(ReflectionProperty $prop, $defaults, &$type)
	{
		$name = $prop->getName();
		if($doc = trim($prop->getDocComment(), " \t\r\n*/")) {
			if(preg_match('/@var[ \t]+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*(:?\[[\s0-9]*\]|)/', $doc, $m)) {
				if($m[2]) {
					$type = 'arr';
				}
				else {
					$type = substr(strtolower($m[1]), 0, 3);
					if($type == 'mix')
						$type = 'str';
					elseif(!in_array($type, self::$knownPHPTypes))
						$type = 'obj';
				}
			}
		}
		
		if(!$type)
			$type = substr(gettype($this->$name), 0, 3);
		
		$nativeType = @self::$nativeTypes[$type == 'str' ? (strlen(''.$defaults[$name]) > 255 ? 'tex' : 'str') : $type];
		
		if(!$nativeType)
			throw new IllegalTypeException('Unstorable type: '.$type.' for property: '.$name);
		
		return $nativeType;
	}
	
	
	/** @return string */
	public function storageClass() {
		return self::$storage->storageClass(get_class($this));
	}
	
	
	/** @return string SQL */
	public function __schema()
	{
		$sql = '';
		$defaults = $this->__reflection()->getDefaultProperties();
		$pk =& $this->primaryKey;
		$pkSql = '';
		
		foreach($this->__reflection()->getProperties() as $prop)
		{
			if($prop->isPublic() && !$prop->isStatic())
			{
				$name = $prop->getName();
				$type = null;
				$nativeType = $this->nativePropertyType($prop, $defaults, $type);
				$defaultValue = $defaults[$name];
				
				$col = '  ' . $name . ' ' . $nativeType;
				if($name == $pk) $col .= ' PRIMARY KEY';
				if($defaultValue !== null) $col .= ' DEFAULT '.self::dbquote($defaultValue, $type);
				$col .= ",\n";
				
				if($name == $pk)
					$pkSql = $col;
				else
					$sql .= $col;
			}
		}
		
		$sql = 'CREATE TABLE ' . $this->storageClass() . " (\n" . rtrim($pkSql . $sql, ", \r\n\t") . "\n);";
		
		return $sql;
	}
	
	
	/** @return ReflectionClass */
	public function __reflection() {
		if(!$this->__reflection)
			$this->__reflection = new ReflectionClass($this);
		return $this->__reflection;
	}
	
	
	/**
	 * @param  string
	 * @return string
	 */
	protected static function fsql($sql /*[, arg1[, arg2[, ...]]]*/)
	{
		if(($argc = func_num_args()) > 1)
		{
			$args = func_get_args();
			array_shift($args);
			
			for($i=1; $i<$argc; $i++) {
				$arg =& $args[$i];
				if(is_string($arg))
					$arg = self::dbquoteStr($arg);
			}
			$sql = vsprintf($sql, $args);
		}
		return $sql;
	}
	
	/**
	 * @param  mixed
	 * @param  string php-type returned from gettype()
	 * @return string
	 * @throws IllegalArgumentException  if a value of type resource or unknown is passed
	 */
	protected static function dbquote($value, $type = null)
	{
		if(!$type)
			$type = gettype($value);
		
		switch($type) {
			case 'str': return "'". self::dbquoteStr($value) ."'";
			case 'int':
			case 'dou': return $value;
			case 'boo': return $value ? 1 : 0;
			case 'NUL': return 'NULL';
			case 'arr':
			case 'obj': return "'". self::dbquoteStr(serialize($value)) ."'";
			default:
				throw new IllegalArgumentException('Can not store data of type: '.$type);
		}
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	protected function dbquoteStr($string) {
		return addslashes($string);
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public static function dbexec($sql) {
		if(!@self::$db->queryExec($sql, $err))
			throw new IOException('Database statement failed to execute: '.$err);
	}
}
?>