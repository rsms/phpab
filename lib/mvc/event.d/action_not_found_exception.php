<?
$html = '<p>No action responded to '.(MVCRouter::$controller ? MVCRouter::$controller->action_name : 'index').'</p>';

if(MVC_DEV_MODE)
	$html .= '<p><small>' . ABException::format($e) . '</small></p>';

http_error('404 Not Found', 'Unknown action', $html);
?>