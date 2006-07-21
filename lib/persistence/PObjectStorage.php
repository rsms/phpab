<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 */
abstract class PObjectStorage {
	
	/** @var PObject */
	private static $refObjs = array();
	
	/** @var string */
	private static $storageClassCache = array();
	
	/** @var string[] */
	protected static $phpTypes = array('str','int','dou','boo','NUL','arr','obj');
	
	/**
	 * Map php types to native DB types
	 * @var array
	 */
	public $nativeTypes = array();
	
	
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
	 * @param  string  PHP classname
	 * @return string
	 */
	public static function storageClass($class) {
		if(!isset(self::$storageClassCache[$class]))
			self::$storageClassCache[$class] = Inflector::tableize($class);
		return self::$storageClassCache[$class];
	}
	
	/**
	 * @param  ReflectionProperty
	 * @param  array (string => string)
	 * @param  string
	 * @return string
	 */
	public function getNativePropertyType(ReflectionProperty $prop, &$defaults, &$type)
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
					elseif(!in_array($type, self::$phpTypes))
						$type = 'obj';
				}
			}
		}
		
		if(!$type)
			$type = substr(gettype(self::referenceObj($prop->getDeclaringClass()->getName())->$name), 0, 3);
		
		$nativeType = $this->nativeTypes[$type == 'str' ? (strlen(''.$defaults[$name]) > 255 ? 'tex' : 'str') : $type];
		
		if(!$nativeType)
			throw new IllegalTypeException('Unstorable type: '.$type.' for property: '.$name);
		
		return $nativeType;
	}
	
	
	/**
	 * @param  PObject
	 * @return PObjectSchema
	 */
	abstract public function schemaForObject(PObject $obj);
	
	
	/**
	 * @param  mixed  string Classname or PObject instance
	 * @param  mixed
	 * @return PObject or null if not found
	 */
	abstract public function find($classOrObj, $id);
	
	
	/**
	 * @param  mixed  string Classname or PObject instance
	 * @return PObject[]
	 */
	abstract public function findAll($classOrObj);
	
	
	/**
	 * @param  PObject
	 * @param  string  Primary key
	 * @return void
	 */
	abstract public function save(PObject $obj, $pk);
	
	
	/**
	 * @param  PObject
	 * @param  string  Primary key
	 * @return void
	 * @throws IllegalOperationException if object is not yet persistent
	 */
	abstract public function delete(PObject $obj, $pk);
}
?>