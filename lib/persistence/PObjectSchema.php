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
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage persistence
 */
class PObjectSchema {
	
	/** @var array (string name => array('type' => string(3), 'default' => mixed), ... ) */
	public $fields;
	
	/** @var string */
	public $primaryKey;
	
	/**
	 * @param  array
	 * @param  string
	 * @param  string
	 */
	public function __construct(&$fields, $pk) {
		$this->fields =& $fields;
		$this->primaryKey = $pk;
	}
	
	/** @return void */
	public function execute() {
		throw new IllegalOperationException('Not implemented');
	}
	
	/** @return string */
	public function toString() {
		return get_class($this).'{'.print_r($this->fields, 1).'}';
	}
	
	/** @return string */
	public function __toString() { return $this->toString(); }
}
?>