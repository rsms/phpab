<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage mvc
 */
class MVCTemplate {
	
	public static $has_rendered = false;
	
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