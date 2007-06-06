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
 * Perform tests in a plain php-file
 * 
 * <b>Example file:</b>
 * <code>
 * function shake_me_baby($str) {
 *   return strrev($str);
 * }
 *
 * $users = array('Johan', 'Eva', 'Kalle');
 *
 * assert('shake_me_baby("Johan") == "nahoJ"');
 * assert('shake_me_baby("Killer") != "Potatis"');
 * assert('in_array("Eva", $users)');
 * assert('!in_array("Sunkarn", $users)');
 * assert('ucfirst($users[0]) == $users[0]');
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitFileTestCase extends UnitTestCase {
	
	/** @var string */
	protected $path = '';
	
	/** @param string */
	public function __construct($path) {
		$this->path = $path;
	}
	
	/** @return void */
	protected function performTests() {
		require_once $this->path;
	}
	
	/**
	 * File path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}
?>