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
class MVCTemplate {
	
	/** @var bool */
	public static $has_rendered = false;
	
	/**
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function render($action, $params = 0)
	{
		self::$has_rendered = true;
		
		# promote params to local scope
		if($params)
			foreach($params as $k => $v)
				if($k != 'controller' && $k != 'action')
					eval("\$$k =& \$params['$k'];");
		
		# promote vars to local scope
		$controller = MVCRouter::$controller;
		
		# promote custom controller properties
		foreach($controller->attributes as $k => $v)
			eval("\$$k =& \$controller->attributes['$k'];");
		
		# get file
		if(isset($params['controller']))
			$file = 'app/views/' . $params['controller'] . '/' . $action . '.phtml';
		else
			$file = 'app/views/' . $controller->name . '/' . $action . '.phtml';
		
		if(!@include APPLICATION_DIR . $file) {
			if(!file_exists($file))
				throw new MVCTemplateNotFoundException("Missing template $file");
			else
				throw new IOException("Failed to access template $file");
		}
	}
}
?>