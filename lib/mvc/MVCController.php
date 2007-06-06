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