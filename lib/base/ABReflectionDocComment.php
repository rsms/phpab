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
 * Represents a programmatically accessible DocComment
 *
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage reflection
 */
class ABReflectionDocComment 
{
	/** @var array */
	protected $info;
	
	/** @var object */
	public $owner;
	
	
	/**
	 * @param  string
	 * @param  object
	 */
	public function __construct( $docComment, $owner=null )
	{
		$this->info = self::parseString($docComment);
		$this->owner = $owner;
	}
	
	
	/**
	 * Get full description
	 * 
	 * @return string  Empty string if no description
	 */
	public function getDescription()
	{
		return isset($this->info['desc']) ? $this->info['desc'] : '';
	}
	
	
	/**
	 * Get the brief part of the description, if any.
	 * Not including detailed description.
	 *
	 * @return string  Empty if no description
	 */
	public function getBriefDescription()
	{
		if(isset($this->info['desc']))
		{
			$desc = &$this->info['desc'];
			if(($p = strpos($desc, "\n")) !== false)
				return trim(substr($desc, 0, $p));
			else
				return trim($desc);
		}
		return '';
	}
	
	
	/**
	 * Get the detailed part of the description, if any.
	 * Not including brief description.
	 *
	 * @return string  Empty if no detailed description
	 */
	public function getDetailedDescription()
	{
		if(isset($this->info['desc']))
		{
			$desc = &$this->info['desc'];
			if(($p = strpos($desc, "\n")) !== false)
				return trim(substr($desc, $p));
		}
		return '';
	}
	
	
	/**
	 * Get any <samp>@</samp>-attribute.
	 *
	 * @param  string  Always lower case. If null is specified, all attributes are returned.
	 * @return array   mixed structure
	 */
	public function getAttributes( $named = null )
	{
		if($named == null)
			return $this->info;
		return isset($this->info[$named]) ? $this->info[$named] : array();
	}
	
	
	/**
	 * Get a list of all available attribute names
	 *
	 * @return string[]
	 */
	public function getAttributeNames() {
		return array_keys($this->info);
	}
	
	
	/**
	 * Get any first found <samp>@</samp>-attribute.
	 *
	 * @param  string  Always lower case
	 * @param  mixed
	 * @return string  or <samp>$default</samp> if none was found
	 */
	public function getAttribute( $named, $default = null )
	{
		if(isset($this->info[$named]))
		{
			$v = $this->info[$named]; # copy
			if(is_array($v))
				return array_shift($v);
			else
				return $v;
		}
		return $default;
	}
	
	
	/**
	 * Get a <samp>@param</samp> attribute.
	 *
	 * @param  string  name...
	 * @param  string  ...or index
	 * @param  mixed
	 * @return array
	 */
	public function getParameter( $named, $orAtIndex = -1, $default = null )
	{
		if(!isset($this->info['param']))
			return $default;
		
		if(isset($this->info['param'][$named]))
			return $this->info['param'][$named];
		
		if(isset($this->info['param'][$orAtIndex]))
			return $this->info['param'][$orAtIndex];
		
		return $default;
	}
	
	
	/**
	 * Parse a doc-comment into a machine readable php array
	 *
	 * Example return:
		 Array
	(
	    [desc] => Get all messages posted by a specified user or by anyone
	If <samp>$userID</samp> is null, all users messages will be returned.
	    [param] => Array
	        (
	            [0] => Array
	                (
	                    [type] => string
	                    [comment] => User-ID
	                )
				
	            [bosse] => Array
	                (
	                    [type] => string
	                    [comment] => Really good boll
	                )
				
	            [3] => Array
	                (
	                    [type] => dummy
	                )
			
	        )
	    [return] => void
	)
	
	 *
	 * @param  string
	 * @return array
	 */
	public static function parseString( $str )
	{	
		$ret = array('desc' => false);
		$attributes = self::parseStringAttributes($str, $ret['desc']);
		
		if(isset($attributes['return']))
			$attributes['return'] = self::correctNamedType($attributes['return'][0]);
		else
			$attributes['return'] = 'mixed';
		
		if(isset($attributes['param']))
		{	
			$params = array();
			foreach($attributes['param'] as $i => $v)
			{	
				if(empty($v))
					continue;
				
				if($v{0} == '$') {
					$p = self::firstGapInString($v);
					$i = substr($v, 1, $p-1);
					#$params[$i]['var'] = substr($v, 1, $p);
					$v = trim(substr($v, $p));
				}
				
				$params[$i] = array('type' => 'mixed');
				
				$p = self::firstGapInString($v);
				if($p === false)
				{	
					$params[$i]['type'] = self::correctNamedType($v);
				}
				else {
					$params[$i]['type'] = self::correctNamedType(substr($v, 0, $p));
					$params[$i]['comment'] = trim(substr($v, $p));
				}
			}
			
			$ret['param'] =& $params;
			unset($attributes['param']);
		}
		
		return array_merge($ret, $attributes);
	}
	
	/**
	 * Parse string attributes & description
	 */
	private static function parseStringAttributes( &$str, &$description )
	{	
		if(empty($str)) {
			return '';
		}
		
		$str = trim(substr($str, self::strnpos($str, '*', 1), -1));
		$len = strlen($str);
		$a = 0;
		$attributeName = false;
		$attributeValue = false;
		$description = false;
		$pairs = array();
		$remFromValue = array("\r", "\n");
		
		for(;$a<$len;$a++)
		{
			$ch = $str{$a};
			
			# trim
			if($ch == "\n")
			{	
				$attributeValue .= "\n";
				
				for(;$a<$len;$a++)
				{	
					$ch = $str{$a};
					if($ch != ' ' && $ch != "\t" && $ch != "\n" && $ch != "\r" && $ch != '*')
						break;
				}
				if($ch == '@') {
					
					# have old attr? add it first:
					if($attributeName)
					{	
						$attributeName = strtolower($attributeName);
						if(!isset($pairs[$attributeName]))
							$pairs[$attributeName] = array();
						$pairs[$attributeName][] = str_replace($remFromValue, '', trim($attributeValue," \t*"));
						$attributeValue = false;
					}
					elseif($attributeValue)
					{
						$description = trim($attributeValue," \t\n\r*");
						$attributeValue = false;
					}
					
					$a++;
					# alloc new attr name
					$attributeName = '';
					for(;$a<$len;$a++)
					{	
						$ch = $str{$a};
						$c = ord($ch);
						if(($c > 47 && $c < 58) || ($c > 64 && $c < 91) || ($c > 96 && $c < 123) || $c == 95)
							$attributeName .= $ch;
						else
							break;
					}
				}
			}
			
			# append to attr val
			$attributeValue .= $ch;
		}
		
		# add last line
		if($attributeName) {
			$attributeName = strtolower($attributeName);
			$pairs[$attributeName][] = trim($attributeValue," \t\n\r*");
		}
		elseif($attributeValue) {
			$attributeName = strtolower($attributeName);
			if(isset($pairs[$attributeName]))
				$pairs[$attributeName][key($pairs[$attributeName])] .= trim($attributeValue," \t\n\r*");
		}
		
		return $pairs;
	}
	
	
	/**
	 * Find first char in string not matching $ch
	 * 
	 * @return  int  position or -1 if not found
	 */
	private static function strnpos($str, $ch, $offset = 0)
	{	
		$len = strlen($str);
		for(;$offset<$len;$offset++)
			if($str{$offset} != $ch)
				return $offset;
		return -1;
	}
	
	
	/**
	 * Transpond typed type to it's correct name
	 *
	 * @param  string
	 * @return string
	 */
	protected static function correctNamedType($type)
	{
		$t = substr(strtolower($type),0,3);
		switch($t) {
			case 'str':	return 'string';
			case 'int': return 'int';
			case 'flo':
			case 'dou':
			case 'rea': return 'double';
			case 'voi': return 'void';
			case 'boo': return 'bool';
			case 'mix': return 'mixed';
			case 'arr': return 'array';
			case 'obj': return 'object';
			case 'res': return 'resource';
			default:    return $type;
		}
	}
	
	
	/**
	 * Find first gap in a string
	 *
	 * @return int
	 */
	private static function firstGapInString(&$string)
	{
		$p = strpos($string,' ');
		if($p===false)
			$p = strpos($string,"\t");
		return $p;
	}
	
	
	/** @ignore */
	public static function __test()
	{
		$c = new ReflectionClass(get_class());
		$doc = new self($c->getDocComment());
		assert($doc->getAttribute('package') == 'ab');
		assert($doc->getAttribute('subpackage') == 'reflection');
		assert($doc->getAttribute('author') == 'Rasmus Andersson  http://hunch.se/');
		assert($doc->getDescription());
		assert($doc->getBriefDescription());
		assert(is_array($doc->getAttributeNames()));
		assert(is_array($doc->getAttributes()));
		assert(is_array($doc->getAttributes('package')));
	}
}
?>