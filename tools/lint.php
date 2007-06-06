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
require_once '../lib/base/boot.php';


$php = Process::resolveBinary('php');
$lib = new File('../lib');
$allOK = true;
$scrollTo = '<script type="text/javascript">window.scrollTo(0,100000);</script>';

foreach($lib->getFiles(true) as $file)
{
	$path = $file->getAbsolutePath();
	
	if(strrchr($path, '.') == '.php')
	{
		$cmd = "$php -l '$path' 2>&1";
		#print "$cmd<br />";
		$out = `$cmd`;
		$shortPath = basename(dirname($path)).'/'.basename($path);
		
		if(strpos($out, 'No syntax errors') !== false) {
			print '<span style="color:#8c8">'.$shortPath.'</span><br />';
		}
		else {
			print '<span style="color:#a00">'.$shortPath.'</span><br />'
				. '<pre style="border:1px solid #ddd;padding:7px;margin-bottom:10px;">'
				. htmlentities($out)
				. '</pre>';
			
			$allOK = false;
		}
		
		print $scrollTo;
		
		flush();
	}
}

if($allOK)
	print '<hr /><h2>All okay!</h2>'.$scrollTo;
else
	print '<hr /><h1>Huston, we have a problem...</h1>'.$scrollTo;

?>