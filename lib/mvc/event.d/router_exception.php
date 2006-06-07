<?
$path = $_SERVER['REQUEST_URI'];
if(($p = strpos($path, '?')) !== false)
	$path = substr($path, 0, $p);

$html = '<p>Recognition failed for '.htmlentities($path).'</p>';
if(MVC_DEV_MODE)
	$html .= '<p><small>' . ABException::format($e) . '</small></p>';

http_error('404 Not Found', 'Routing Error', $html);
?>