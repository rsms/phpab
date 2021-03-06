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
 * MVC request handler
 *
 * @version    $Id$
 * @package    ab
 * @subpackage mvc
 */
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
	
	$_mvc_dev_can_print = null;
	
	/**
	 * @return bool
	 * @ignore
	 */
	function mvc_dev_can_print() {
		global $_mvc_dev_can_print;
		if($_mvc_dev_can_print !== null)
			return $_mvc_dev_can_print;
		foreach(headers_list() as $h)
			if(strcasecmp(substr($h,0,12), 'content-type') == 0)
				if(stripos($h, 'text/html') === false)
					return $_mvc_dev_can_print = false;
		return $_mvc_dev_can_print = true;
	}
	
	/**
	 * @return void
	 * @ignore
	 */
	function benchmark_timer_stop() {
		if(!mvc_dev_can_print())
			return;
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