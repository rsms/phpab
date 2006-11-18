<?
/**
 * @package    ab
 * @subpackage transcode
 * @version    $Id$
 * @author     Rasmus Andersson
 */
class TCSyntaxHighlighter {
	
	/**
	 * @param  string  ".php"
	 * @return TCSyntaxHighlighter
	 */
	public static function parserForExtension($ext) {
		switch($ext) {
			case '.php':
			case '.inc':
			case '.tpl':
			case '.phtml':
				return new TCSyntaxHighlighter();
			
			case '.txt':
			case '.log':
			case '.info':
			case '.text':
			case '.nfo':
				return new TCDummySyntaxHighlighter();
			
			default:
				return new TCEnscriptSyntaxHighlighter();
		}
	}
	
	/**
	 * @param  string
	 * @param  string  File extension including a dot. ie. ".html"
	 * @param  string
	 * @return string
	 */
	public static function highlightString($code, $ext = '.php', $output = 'html') {
		return self::parserForExtension($ext)->convertString($code, $ext, $output);
	}
	
	/**
	 * @param  string  File path
	 * @param  string
	 * @return string
	 */
	public static function highlightFile($path, $output = 'html') {
		return self::parserForExtension($ext)->convertFile($path, $output);
	}
	
	/**
	 * @param  string
	 * @param  string  File extension including a dot. ie. ".html"
	 * @param  string
	 * @return string
	 */
	public function convertString($code, $ext = '.php', $output = 'html')
	{
		self::setupPHP();
		return $this->postProcessPHPHtml(highlight_string($code,1));
	}
	
	/**
	 * @param  string
	 * @param  string  File extension including a dot. ie. ".html"
	 * @return string
	 */
	public function convertFile($path, $output = 'html')
	{
		self::setupPHP();
		return $this->postProcessPHPHtml(highlight_file($path,1));
	}
	
	/** @return void */
	private static function setupPHP() {
		ini_set('highlight.comment', 	'#999');
		ini_set('highlight.default', 	'#000;font-weight:bold');
		ini_set('highlight.html', 		'#666');
		ini_set('highlight.keyword', 	'#56b;font-weight:bold');
		ini_set('highlight.string', 	'#080');
	}
	
	/** @return string */
	private function postProcessPHPHtml($html)
	{
		/*if(preg_match(
			'/(class(&nbsp;)+)<\/span><span[^>]*>([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(&nbsp;)+<\/span>/',
			$html, $m)) {
			die(print_r($m,1));
		}*/
		
		return preg_replace(array(
			'/(function(&nbsp;)+)<\/span><span[^>]*>([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)<\/span>/',
			'/(class(&nbsp;)+)<\/span><span[^>]*>([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(&nbsp;)*)<\/span>/',
			'/<span[^>]*>(self)<\/span>/'
			),array(
			'$1<span class="function">$3</span></span>',
			'$1<span class="class">$3</span></span>',
			'<span class="keyword">$1</span></span>',
			),
			$html);
	}
}
?>