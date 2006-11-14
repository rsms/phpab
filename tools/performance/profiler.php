<?
apd_set_pprof_trace();
require_once '../../lib/base/boot.php';

$f = new File(__FILE__);
var_dump($f->isDir());
var_dump($f->isFile());
$f->copyTo('/tmp/test');

#$t = getrusage();
#$t = doubleval($t['ru_utime.tv_sec']*1000) + doubleval($t['ru_utime.tv_usec']/1000000);
#printf("User time: %f", $t);

?>
