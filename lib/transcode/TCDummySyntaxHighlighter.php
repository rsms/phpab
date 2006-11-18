<?
/**
 * @package    ab
 * @subpackage transcode
 * @version    $Id$
 * @author     Rasmus Andersson
 */
class TCDummySyntaxHighlighter extends TCSyntaxHighlighter {

	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function convertString($code, $ext = '', $output = 'html') {
		return ($output == 'html') ? htmlentities($code) : $code;
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function convertFile($path, $output = 'html') {
		return ($output == 'html') ? htmlentities(file_get_contents($path)) : file_get_contents($path);
	}
}
?>