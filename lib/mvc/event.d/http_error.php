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
 * @package    ab
 * @subpackage mvc
 */

if(!$title)
	$title = $status;

/*while(ob_get_level() > 0)
	ob_end_clean();*/

if(MVC_DEV_MODE) {
	$html .= '<p><small><b>Request:</b><pre>';
	foreach($_SERVER as $k => $v)
		if(!is_array($v))
			$html .= sprintf("% -20s  %s\n", $k, htmlentities(trim($v)));
	$html .= '</pre></small></p>';
}

header('Status: '.$status);
print '<html><head><title>'.$title.'</title></head><body>'
	. '<h1>'.$title.'</h1>'.$html.'<hr /><address>hunch.ab.mvc/0.1'
	. (@$_SERVER['SERVER_SIGNATURE'] ? ' - ' . strip_tags($_SERVER['SERVER_SIGNATURE']) : '');

#stop timer if dev
if(MVC_DEV_MODE) benchmark_timer_stop();

print '</address></body></html>';
exit;
?>