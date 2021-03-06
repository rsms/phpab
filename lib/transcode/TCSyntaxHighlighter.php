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