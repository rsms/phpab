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
class TCEnscriptSyntaxHighlighter extends TCSyntaxHighlighter {
	
	/** @var array */
	protected static $ext = array(
		'.adb'     => 'ada',
		'.ads'     => 'ada',
		'.awk'     => 'awk',
		'.c'       => 'c',
		'.c++'     => 'cpp',
		'.cc'      => 'cpp',
		'.cpp'     => 'cpp',
		'.csh'     => 'csh',
		'.cxx'     => 'cpp',
		'.diff'    => 'diffu',
		'.dpr'     => 'delphi',
		'.el'      => 'elisp',
		'.eps'     => 'postscript',
		'.f'       => 'fortran',
		'.for'     => 'fortran',
		'.gs'      => 'haskell',
		'.h'       => 'c',
		'.hpp'     => 'cpp',
		'.hs'      => 'haskell',
		'.htm'     => 'html',
		'.html'    => 'html',
		'.idl'     => 'idl',
		'.java'    => 'java',
		'.js'      => 'javascript',
		'.lgs'     => 'haskell',
		'.lhs'     => 'haskell',
		'.m'       => 'objc',
		'.m4'      => 'm4',
		'.man'     => 'nroff',
		'.nr'      => 'nroff',
		'.p'       => 'pascal',
		'.pas'     => 'delphi',
		'.patch'   => 'diffu',
		'.pkg'     => 'sql', 
		'.pl'      => 'perl',
		'.plist'   => 'html',
		'.pm'      => 'perl',
		'.pp'      => 'pascal',
		'.ps'      => 'postscript',
		'.py'      => 'python',
		'.s'       => 'asm',
		'.scheme'  => 'scheme',
		'.scm'     => 'scheme',
		'.scr'     => 'synopsys',
		'.sh'      => 'sh',
		'.shtml'   => 'html',
		'.sql'     => 'sql',
		'.st'      => 'states',
		'.syn'     => 'synopsys',
		'.synth'   => 'synopsys',
		'.tcl'     => 'tcl',
		'.tex'     => 'tex',
		'.texi'    => 'tex',
		'.texinfo' => 'tex',
		'.v'       => 'verilog',
		'.vba'     => 'vba',
		'.vh'      => 'verilog',
		'.vhd'     => 'vhdl',
		'.vhdl'    => 'vhdl',
		'.xml'     => 'html',
		'.xsl'     => 'html',
		'.ada'     => 'ada',
	);
	
	/**
	 * @param  string
	 * @param  string  File extension including a dot. ie. ".html"
	 * @param  string
	 * @return string
	 */
	public function convertString($code, $ext = '.php', $output = 'html')
	{
		$path = tempnam('/tmp', 'enscript.');
		file_put_contents($path, $code);
		$out = $this->processFile($path, $ext, $output);
		@unlink($path);
		return $out;
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function convertFile($path, $output = 'html')
	{
		return $this->processFile($path, strtolower(strrchr($path,'.')), $output);
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	private function processFile($path, $ext, $output)
	{
		$l = isset(self::$ext[$ext]) ? self::$ext[$ext] : 'txt';
		
		$out = Process::exec('enscript',
			'--language=html '
			.($l ? "--color --pretty-print=$l" : '')
			." -o - '$path' | /bin/sed -n "
			."'1,/^<PRE.$/!{/^<\\/PRE.$/,/^<PRE.$/!p;}'");
		
		if($l=='cpp'||$l=='c'||$l=='java') 
		{
		$out = str_replace(
			array('#5F9EA0','#A020F0','#BC8F8F','#228B22','#B22222','synchronized('), 
			array('#223388','#1111ff','#228822','#223388','#777777',
				  '<FONT COLOR="#1111ff">synchronized</FONT>('), 
			$out);
		}
		
		$out = str_replace(
			array("\n"), 
			array("<br />\n"), 
			$out
		);
		
		$out = preg_replace(
			array('/(<[^>]+>.*)\t(.*<[^>]>)/msU', '/(>.*)\s(.*<)/'),
			array('$1&nbsp;&nbsp;&nbsp;$2', '$1&nbsp;$2'),
			$out
		);
		
		$out = str_replace('<br />&nbsp;', '<br />', $out);
		
		return '<code>'.$out.'</code>';
	}
}
?>