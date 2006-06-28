<?
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