<?
/**
 * Lists all available mcrypt algorithms, all their respective modes as well as block, iv and key size.
 * Can be ran in a browser or on the command line
 */
header('Content-type: text/plain');

print "ALGORITHM: mode(block size, iv size, key size)[, mode(block size, iv size, key size)[, ...]]\n"
	. "-------------------------------\n";

foreach(mcrypt_list_algorithms() as $a) {
	print strtoupper($a).':';
	foreach(mcrypt_list_modes() as $m) {
		if(@mcrypt_get_block_size($a,$m)) {
			print ' '.$m.'('
				. mcrypt_get_block_size($a,$m) . ','
				. mcrypt_get_iv_size($a,$m) . ','
				. mcrypt_get_key_size($a,$m) . ')';
		}
	}
	print "\n";
}

?>