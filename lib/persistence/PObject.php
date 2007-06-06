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
 * Persistent object base class
 *
 * <b>Example:</b>
 * <code>
 * class AdminUser extends PObject {
 *   public $alias = '';
 *   public $passwd = '';
 * }
 *
 * PObject::$storage = new PObjectSQLiteStorage('/tmp/objects.db');
 * 
 * $user = new AdminUser();
 * print $user->getSchema() . "\n\n";
 * var_dump($o);
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 * @see        PObjectStorage
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
	 * @param  array  Associative array with initial properties
	 */
	public function __construct($properties = array()) {
		foreach(array_keys($properties) as $k)
			$this->$k =& $properties[$k];
	}
	
	/**
	 * @param  string
	 * @param  mixed
	 * @return PObject or null if not found
	 */
	public static function find($class, $id) {
		return self::$storage->find($class, $id);
	}
	
	/**
	 * @param  string
	 * @return PObject[]
	 */
	public static function findAll($class) {
		return self::$storage->findAll($class);
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
	public function getNativePropertyType($property) {
		$type = null;
		$defaults = $this->__reflection()->getDefaultProperties();
		return self::$storage->getNativePropertyType($this->__reflection()->getProperty($property), $defaults, $type);
	}
	
	
	/** @return string */
	public function getStorageClass() {
		return self::$storage->storageClass(get_class($this));
	}
	
	
	/** @return PObjectSchema */
	public function getSchema() {
		return self::$storage->schemaForObject($this);
	}
	
	
	/** @return ReflectionClass */
	public function __reflection() {
		if(!$this->reflectionCache)
			$this->reflectionCache = new ReflectionClass($this);
		return $this->reflectionCache;
	}
	
	public static function __primaryKey() {
		return self::$primaryKey;
	}
}
?>