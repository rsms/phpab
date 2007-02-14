<?
/**
 * Extended class reflection
 *
 * @version    $Id$
 * @author     Rasmus Andersson http://hunch.se/
 * @package    ab
 * @subpackage reflection
 */
class ABReflectionClass extends ReflectionClass
{
	/** @var ABReflectionDocComment */
	protected $docComment = null;
	
	/**
	 * Documentation comment
	 *
	 * @return ABReflectionDocComment
	 */
	public function getDocComment()
	{
		if($this->docComment === null)
			$this->docComment = new ABReflectionDocComment(parent::getDocComment());
		return $this->docComment;
	}
	
	/**
	 * @param  bool
	 * @return string
	 */
	public function getPackageName($tryParseDocComments=true)
	{
		if($tryParseDocComments)
		{
			$doc = $this->getDocComment();
			if($package = trim($doc->getAttribute('package','').'.'.$doc->getAttribute('subpackage',''),'.'))
			return $package;
		}
		
		$path = $this->getFileName();
		$base = AB_LIB;
		$len = strlen($base);
		
		if(substr($path, 0, $len) == $base)
			return str_replace('/','.',dirname(substr($path, $len + (($base{$len-1} != '/') ? 1 : 0))));
		else
			return basename(dirname($path));
	}
	
	/** @ignore */
	public static function __test()
	{
		$c = new self(get_class());
		assert($c->getPackageName() == 'ab.reflection');
		assert($c->getDocComment() instanceof ABReflectionDocComment);
	}
}
?>