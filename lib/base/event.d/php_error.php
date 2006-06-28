<?
/**
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

#onPHPError( $errno, $str, $file, $line, &$context )

# if something was prepended by @, errlevel will be 0
if(error_reporting() == 0)
	return;

if($errno == E_WARNING || $errno == E_USER_WARNING)
	throw new PHPException($str, $errno, $file, $line);

$fileLine = "on line $line in ";
if(isset($_SERVER['DOCUMENT_ROOT']))
	$fileLine .= Utils::relativePath($file, $_SERVER['DOCUMENT_ROOT']);
elseif(isset($GLOBALS['argv'][0]))
	$fileLine .= Utils::relativePath($file, dirname($GLOBALS['argv'][0]));
else
	$fileLine .= $file;

switch($errno) {
	case E_PARSE:
	case E_USER_ERROR:
	case E_ERROR:
		break;
	case E_NOTICE:
	case E_USER_NOTICE:
		if(PHP::isCLI())
			Utils::printError("{$GLOBALS['argv'][0]}: WARNING: $str $fileLine\n");
		else
			print "<span class=\"warning\"><b>WARNING:</b> $str <span class=\"file\">$fileLine</span></span><br />";
		return;
}

if(PHP::isCLI()) {
	Utils::printError("{$GLOBALS['argv'][0]}: FATAL: $str $fileLine\n\t"
		. str_replace("\n","\n\t",ABException::formatTrace(new Exception(), false, array('__errhandler')))
		. "\n");
}
else {
	print "<div class=\"err\"><b>FATAL:</b> $str <span class=\"file\">$fileLine</span>\n"
		. '<div class="trace">' . ABException::formatTrace(new Exception(), true, array('__errhandler')) . '</div>'
		. '</div>';
}

exit(1);
?>