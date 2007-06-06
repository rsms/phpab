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
abstract class PObjectSQLStorage extends PObjectStorage {
	
	/**
	 * Map php types to native DB types
	 * @var array
	 */
	public $nativeTypes = array(
		'boo' => 'TINYINT(1)',
		'int' => 'INT(11)',
		'dou' => 'DOUBLE',
		'str' => 'VARCHAR(255)',
		'tex' => 'TEXT',
		'bin' => 'BLOB',
		'arr' => 'TEXT',
		'obj' => 'TEXT');
	
	
	/**
	 * @param  PObject
	 * @return PObjectSQLSchema
	 */
	public function schemaForObject(PObject $obj)
	{
		$sql = '';
		$defaults = $obj->__reflection()->getDefaultProperties();
		$pk = $obj->__primaryKey();
		$pkSql = '';
		$schemaFields = array();
		
		foreach($obj->__reflection()->getProperties() as $prop)
		{
			if($prop->isPublic() && !$prop->isStatic())
			{
				$name = $prop->getName();
				$type = null;
				$nativeType = $this->getNativePropertyType($prop, $defaults, $type);
				$defaultValue = $defaults[$name];
				
				$col = '  ' . $name . ' ' . $nativeType;
				if($name == $pk) $col .= ' PRIMARY KEY';
				elseif($defaultValue !== null) $col .= ' DEFAULT '.$this->dbquote($defaultValue, $type);
				$col .= ",\n";
				
				if($name == $pk)
					$pkSql = $col;
				else
					$sql .= $col;
				
				$schemaFields[$name] = array('type' => $type, 'default' => $defaultValue);
			}
		}
		
		$sql = 'CREATE TABLE ' . $obj->getStorageClass() . " (\n" . rtrim($pkSql . $sql, ", \r\n\t") . "\n);";
		
		return new PObjectSQLSchema($schemaFields, $pk, $sql, $this);
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
		
		$pk = $classOrObj->__primaryKey();
		
		if($rows = $this->dbquery($this->fsql("SELECT * FROM %s WHERE %s = '%s'", $classOrObj->getStorageClass(), $pk, $classOrObj->$pk)))
			return new $class($rows[0]);
		
		return null;
	}
	
	/**
	 * @param  mixed  string Classname or PObject instance
	 * @param  string Name of an instance property on which to sort
	 * @param  bool   Sort descending instead of ascending (only used if $orderByProperty is specified)
	 * @param  int    Max number of objects to find. 0 = unlimited
	 * @return PObject[]
	 */
	public function findAll($classOrObj, $orderByProperty = '', $orderDesc = false, $limit = 0)
	{
		if(is_string($classOrObj)) {
			$class = $classOrObj;
			$classOrObj = self::referenceObj($class);
		}
		else
			$class = get_class($classOrObj);
		
		$pk = $classOrObj->__primaryKey();
		$limit = $limit ? ' LIMIT 0,'.$limit : '';
		$order = $orderByProperty ? ' ORDER BY '.$orderByProperty.($orderDesc ? 'DESC' : 'ASC') : '';
		$rows = $this->dbquery($this->fsql("SELECT * FROM %s WHERE %s = '%s'".$order.$limit, $classOrObj->getStorageClass(), $pk, $classOrObj->$pk));
		$num_rows = count($rows);
		$objects = array();
		
		for($i=0; $i<$num_rows; $i++)
			$objects[] = new $class($rows[$i]);
		
		return $objects;
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
				$values[] = $this->dbquote($v);
			}
			$this->dbexec('INSERT INTO '.$obj->getStorageClass().' ('.implode(',', $keys).') VALUES ('.implode(',', $values).')');
		}
		else {
			$sql = 'UPDATE '.$obj->getStorageClass().' SET ';
			
			foreach($obj->getProperties(false) as $k => $v)
				$sql .= $k . '=' . $this->dbquote($v) . ',';
			
			$sql = rtrim($sql, ','). " WHERE $pk='".$obj->$pk."'";
			$this->dbexec($sql);
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
		$this->dbexec($this->fsql("DELETE FROM %s WHERE %s = '%s'", $obj->getStorageClass(), $pk, $obj->$pk));
	}
	
	
	/**
	 * @param  string
	 * @return string
	 */
	protected function fsql($sql /*[, arg1[, arg2[, ...]]]*/)
	{
		if(($argc = func_num_args()) > 1)
		{
			$args = func_get_args();
			array_shift($args);
			
			for($i=1; $i<$argc; $i++) {
				$arg =& $args[$i];
				if(is_string($arg))
					$arg = $this->dbquoteStr($arg);
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
	protected function dbquote($value, $type = null)
	{
		if(!$type)
			$type = substr(gettype($value), 0, 3);
		
		switch($type) {
			case 'str': return "'". $this->dbquoteStr($value) ."'";
			case 'int':
			case 'dou': return $value;
			case 'boo': return $value ? 1 : 0;
			case 'NUL': return 'NULL';
			case 'arr':
			case 'obj': return "'". $this->dbquoteStr(serialize($value)) ."'";
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
	 * @throws IOException
	 */
	abstract public function dbexec($sql);
	
	/**
	 * @param  string
	 * @return array
	 * @throws IOException
	 */
	abstract public function dbquery($sql);
}
?>