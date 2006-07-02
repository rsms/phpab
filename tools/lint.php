<?
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