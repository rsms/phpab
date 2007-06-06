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
 * Please understand that calling this method is VERY expensive.
 * So, if you got any warnings (E_NOTICE) at all, fix them!
 * 
 * @version    $Id$
 * @package    ab
 * @subpackage base
 */

#onPHPError( $errno, $str, $file, $line, &$context )

if($errno == E_WARNING || $errno == E_USER_WARNING)
	throw new PHPException($str, $errno, $file, $line);

$fileLine = "on line $line in ";
if(isset($_SERVER['DOCUMENT_ROOT']))
	$fileLine .= File::relativePath($file, $_SERVER['DOCUMENT_ROOT']);
elseif(isset($GLOBALS['argv'][0]))
	$fileLine .= File::relativePath($file, dirname($GLOBALS['argv'][0]));
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
			IO::writeError("{$GLOBALS['argv'][0]}: WARNING: $str $fileLine\n");
		else
			print "<span class=\"warning\"><b>WARNING:</b> $str <span class=\"file\">$fileLine</span></span><br />";
		return;
}

if(PHP::isCLI()) {
	IO::writeError("{$GLOBALS['argv'][0]}: FATAL: $str $fileLine\n\t"
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