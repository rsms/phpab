<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage mvc
 */
class MVCRouter {
	
	/** @var array */
	public static $routes = array();
	
	/** @var MVCController */
	public static $controller = null;
	
	/** @var MVCRoute */
	public static $route = null;
	
	/**
	 * @return MVCRoute
	 */
	public static function findDestination($path)
	{
		self::$route = new MVCRoute();
		$matched_pattern = false;
		
		# match routes
		foreach(self::$routes as $pattern => $destination_params)
		{
			# concat request with destination params
			if($destination_params)
				$_REQUEST = array_merge($_REQUEST, $destination_params);
			
			if($pattern && $pattern{0} == '@') {
				if($matched_pattern = preg_match($pattern, $path, $_REQUEST))
					break;
			}
			else {
				if($pattern && $pattern != '/') {
					# no keywords?
					if(strpos($pattern, ':') === false)
					{
						# plain match
						if($matched_pattern = (strcasecmp(rtrim($path, '/'), rtrim($pattern, '/')) == 0))
							break;
					}
					else {
						# keyword match
						if($matched_pattern = self::pathMatch($pattern, $path, $_REQUEST))
							break;
					}
					
				}
				else {
					if($matched_pattern = ($path == '/'))
						break;
				}
			}
		}
		
		
		if(!$matched_pattern)
			$_REQUEST['controller'] = 'ApplicationController';
		
		return self::$route;
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return bool
	 */
	public static function pathMatch($pattern, $path, &$matches)
	{
		$matches_backup = $matches;
		$is_match = false;
		$patternv = explode('/', trim($pattern, '/'));
		$path = trim($path, '/');
		if(!$path)
			return $pattern ? false : true;
		
		$pathv = explode('/', $path);
		$len = count($patternv);
		
		for($i=0;$i<$len;$i++)
		{
			$p =& $pathv[$i];
			
			if(isset($patternv[$i]))
			{
				if($patternv[$i]{0} == ':') {
					$matches[substr($patternv[$i], 1)] = $p;
					$is_match = true;
				}
				elseif($patternv[$i] != $p) {
					$matches =& $matches_backup;
					return false;
				}
			}
		}
		return $is_match ? true : false;
	}
}


/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage mvc
 */
class MVCRoute {
	
	/**
	 * @return void
	 */
	public function execute()
	{
		if(!isset($_REQUEST['controller']))
			throw new MVCRouterException('Missing controller');
		
		# get controller name
		$controller_name =& $_REQUEST['controller'];
		if(strpos($controller_name,'_') !== false)
			$controller_name = strtolower($controller_name);
		elseif(ctype_upper($controller_name{0}))
			$controller_name = Inflector::underscore($controller_name);
		
		# get controller class
		$controller_class = Inflector::camelize($controller_name) . 'Controller';
		
		# require app controller
		if(!@include_once(APPLICATION_DIR . 'app/controllers/application.php')) {
			error_log("No application controller found! Looked for ".APPLICATION_DIR . 'app/controllers/application.php');
			throw new MVCRouterException('No application controller found!');
		}
		
		# load controller
		if(!@include_once APPLICATION_DIR . 'app/controllers/' . $controller_name . '_controller.php')
			throw new MVCRouterException("Can not find controller '$controller_name'");
		
		# instantiate controller
		$controller = new $controller_class;
		MVCRouter::$controller = $controller;
		
		# set controller properties
		$controller->name = $controller_name;
		$controller->action_name = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
		
		# execute action
		ob_start();
		try {
			$_action = $controller->action_name;
			$controller->$_action;
			#call_user_func(array($controller, $controller->action_name));
			
			# render template (if not allready explicitly rendered by controller)
			if(!MVCTemplate::$has_rendered) {
				# we want MVCTemplate to use MVCRouter::$controller instead of controller from request
				if(isset($_REQUEST['controller']))
					unset($_REQUEST['controller']);
				
				MVCTemplate::render($controller->action_name, $_REQUEST);
			}
			
			# finalize response
			$content_for_layout = ob_get_clean();
			if(!@include APPLICATION_DIR . 'app/views/layouts/' . $controller->name . '.phtml')
			{
				# Missing layout for controller - render content directly
				print $content_for_layout;
			}
		}
		catch(Exception $e) {
			ob_end_clean();
			throw $e;
		}
	}
}
?>