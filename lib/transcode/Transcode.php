<?
/**
 * Translates and optimises code
 *
 * @package    ab
 * @subpackage transcode
 * @version    $Id$
 * @author     Rasmus Andersson
 */
class Transcode {
	
	/**
	 * @param  string  File path
	 * @return string  HTML/PHP
	 */
	public static function convertJSPFileToPHP($file) {
		return self::convertJSPToPHP(file_get_contents($file));
	}
	
	/**
	 * @param  string  JSP/HTML/... code
	 * @return string  HTML/PHP
	 */
	public static function convertJSPToPHP($code)
	{
		$trans = array(
			# General rules
			'/<%@[\s\r\n]*include[\s\r\n]+file="(.+)"[\s\r\n]*%>/m' => '<? include \'$1\'; ?>',
			'/include \'\//' => 'include WWWDIR.\'/',
			'/[\n\r]*<%@[^>]+WEB-INF[^>]+>[\n\r]*/ms' => '',
			'/[\n\r]*<%@[\s\r\n]*page[\s\r\n]+import="[^"]*"[\s\r\n]*%>[\n\r]*/m' => '',
			'/[\n\r]*<%--[\r\n\s\t]*--%>[\n\r]*/m' => '',
			'/<%--(.+)--%>/Ums' => '<?/*$1*/?>',
			'/\.jsp/m' => '.php',
			
			# member/inc/userident
			'@[\s\r\n]*include WWWDIR.\'/member/inc/userident.php\';[\s\r\n]*@m' => '',
			
			# Banner
			'/[\s\r\n]*Banner2k\s+([\w_]+)\s*=\s*new\s+Banner2k\(\s*request\s*\)[\s\r\n]*/m' => '',
			
			# User management
			'/<%[\s\r\n]+User\s+([\w_]+)\s*=\s*HttpUserManager\.getCurrentUser\(\s*request\s*\)[;\s\r\n]*%>/m' => '<? $$1 = SprayUser::getLoggedIn(); ?>',
			
			# Finish him!
			'/(<\?[^=].*)\s*\?><\?\s*([^=])/Ums' => '$1 $2',
			'/<\?[\r\n\s]*\?>/m' => '',
			'/\?><!DOCTYPE/' => "?>\n<!DOCTYPE",
		);
		return preg_replace(array_keys($trans), $trans, $code);
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return TranscodeMessage[]
	 */
	public static function optimizeHTML(&$html, $outFile = null)
	{
		$writeFile = true;
		if($outFile === null) {
			$outFile = File::createTempFile()->getPath();
			$writeFile = false;
		}
		
		# tidy
		$msgs = self::tidy($html, $outFile);
		
		# PHP syntax check
		$msgs = array_merge($msgs, self::checkPHPSyntax($outFile));
		
		# remove redundant spaces/linebreaks
		$trans = array(
			'/<\?[\s\n\r]*\/\*.+\*\/[\s\n\r]*\?>/Ums' => '',
			'/<!--(.+)-->/Ums' => '',
			'/>[\r\n\s\t]+</m' => '><',
		);
		$html = preg_replace(array_keys($trans), $trans, $html);
		
		# write file
		if($writeFile)
			file_put_contents($outFile, $html);
		
		return $msgs;
	}
	
	/**
	 * @param  string
	 * @return TranscodeTidyMessage[]
	 */
	private static function tidy($html, $file)
	{
		file_put_contents($file, $html);
		$html_lines = null;
		
		$msgs = array();
		foreach(explode("\n",`/usr/bin/tidy -c -m -i -asxhtml -latin1 -w 0 -q "$file" 2>&1`) as $line)
		{
			$line = trim($line);
			if($line == '')
				continue;
			
			if(preg_match('/line (\d+) column (\d+) - (\w+): (.+)/', $line, $m))
			{
				$class = 'TranscodeTidy'.$m[3];
				$line = intval($m[1]);
				$column = intval($m[2]);
				$msg = $m[4];
				$code = '';
				
				if($line) {
					if($html_lines === null)
						$html_lines = explode("\n", $html);
				
					$code = $html_lines[$line-1];
					if(preg_match('/^([\s\t]+)(.+)/', $code, $m)) {
						$column -= strlen($m[1]);
						$code = $m[2];
					}
				}
				
				$msgs[] = new $class($msg, $line, $column, $code);
			}
			else {
				$msgs[] = new TranscodeTidyMessage($line, 0, 0, '');
			}
		}
		
		$html = trim(file_get_contents($file));
		return $msgs;
	}
	
	/**
	 * @param  string
	 * @return TranscodePHPLintMessage[]
	 */
	public static function checkPHPSyntax($file) {
		$out = str_replace(array(' in '.$file, "\nErrors parsing $file"),'',trim(`/usr/bin/php -l "$file" 2>&1`));
		return array(new TranscodePHPLintMessage($out));
	}
}
?>