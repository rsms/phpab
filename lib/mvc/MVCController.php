<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage mvc
 */
class MVCController {
	
	/** @var string */
	public $name = '';
	
	/** @var string */
	public $action_name = 'index';
	
	/**
	 * Custom properties
	 * Can be accessed using syntax: $controller_instance->property_name
	 *
	 * @var array
	 */
	public $attributes = array();
	
	
	/** @ignore */
	public function __get($key)
	{
		# call action and remember name
		if(in_array($key.'_', get_class_methods($this))) {
			$this->action_name = $key;
			$method = $key.'_';
			return $this->$method();
			#return call_user_func(array($this, $key.'_'));
		}
		
		if(isset($this->attributes[$key]))
			return $this->attributes[$key];
		
		throw new MVCActionNotFoundException($key);
	}
	
	/** @ignore */
	public function __set($key, $value)
	{
		$this->attributes[$key] = $value;
	}
}
?>