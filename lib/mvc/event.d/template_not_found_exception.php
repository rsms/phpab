<?
/**
 * @version    $Id$
 * @package    ab
 * @subpackage mvc
 */
$html = '<p>'.$e->getMessage().'</p>';

if(MVC_DEV_MODE)
	$html .= '<p><small>' . ABException::format($e) . '</small></p>';

http_error('404 Not Found', 'Template is missing', $html);
?>