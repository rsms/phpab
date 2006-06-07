<?

$html = '<pre>'.strip_tags(get_class($e) . " from " . $e->getFile().':'.$e->getLine().': '.$e->getMessage());
$errmsg = "Killed by ".strip_tags(get_class($e) . " from " . $e->getFile().':'.$e->getLine().': '.$e->getMessage());

if(MVC_DEV_MODE) {
	if($e instanceof ABException)
		$ts = ABException::formatTrace($e, false);
	else
		$ts = $e->getTraceAsString();
	$errmsg .= "\n   ".strip_tags(str_replace("\n","\n   ",$ts));
	$html .= "\n<small>\n   ".strip_tags(str_replace("\n","\n   ",$ts)).'</small>';
}

http_error('500 Internal Error', 0, $html . '</pre>');
?>