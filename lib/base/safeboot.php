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
 * Abstract Base bootstrap for Safe Mode
 *
 * Activates logic to simulate stuff you can't do when you are 
 * running PHP in safe-mode.
 *
 * <b>Note:</b> Loading the safe bootstrap is about 5 times slower 
 *              than loading the normal bootstrap {@link boot.php}
 *
 * @version    $Id$
 * @package    ab
 * @subpackage base
 * @see        boot.php
 */

# Classpath
$__CP = array();

/**
 * @param  string
 * @return void
 * @ignore
 */
function __autoload($c)
{
	global $__CP;
	foreach($__CP as $d) {
		if((@include_once "$d/$c.php") !== false) {
			return;
		}
	}
	$t = debug_backtrace();
	if(@$t[1]['function'] != 'class_exists') {
		require_once 'event.d/autoload_failure.php';
	}
}

/** @ignore */
function import( $dirpath ) {
	global $__CP;
	$__CP[] = $dirpath;
}

define('SAFEMODE',1);
require_once 'boot.php';
?>