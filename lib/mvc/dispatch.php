<?
########################################################################
# Helpers

/**
 * @param  string
 * @param  array
 * @return void
 */
function render($template_name, $params = 0) {
	MVCTemplate::render($template_name, $params);
}

/**
 * @param  string
 * @return void
 */
function render_text($text) {
	MVCTemplate::$has_rendered = true;
	print $text;
}

/**
 * @param  string
 * @return string
 */
function h($text) {
	return htmlentities($text);
}

/**
 * @param  int
 * @param  string
 * @param  string
 * @return void
 */
function http_error($status, $title, $html) {
	require_once MVC_DIR.'event.d/http_error.php';
}

########################################################################

# init dev stuff - we keep it here instead of event.d because the dev performance is important.
if(MVC_DEV_MODE) {
	$benchmarkTimer = new BenchmarkTimer();
	BenchmarkTimer::$utf8 = 0;
	$benchmarkTimer->start();
	
	function benchmark_timer_stop() {
		global $benchmarkTimer;
		print ' - '.$benchmarkTimer->stop().', '.intval(1.0 / $benchmarkTimer->getRealTime()).' requests/second';
	}
}

########################################################################

define('MVC_DIR', APPLICATION_DIR.'lib/mvc/');

try {
	MVCRouter::findDestination(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/')->execute();
}
catch(MVCTemplateNotFoundException $e){ require_once MVC_DIR.'event.d/template_not_found_exception.php'; } 
catch(MVCActionNotFoundException $e) {	require_once MVC_DIR.'event.d/action_not_found_exception.php'; } 
catch(MVCRouterException $e) {	        require_once MVC_DIR.'event.d/router_exception.php'; }
catch(MVCException $e) { 		        require_once MVC_DIR.'event.d/mvc_exception.php'; }
catch(Exception $e) {				    require_once MVC_DIR.'event.d/exception.php'; }

# stop timer if dev
if(MVC_DEV_MODE)
	benchmark_timer_stop();

?>