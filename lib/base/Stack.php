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
 * Simple stack
 *
 * @version    $Id$
 * @author     Rasmus Andersson http://hunch.se/
 * @package    ab
 * @subpackage util
 */
class Stack {
	
	/** @var int */
	public $size = 0;
	
	/** @var array */
	public $items = array();
	
	/**
	 * @param  mixed
	 * @return void
	 */
	public function push($item) {
		$this->items[$this->size] = $item;
		$this->size++;
	}
	
	/**
	 * @return mixed  Null if stack is empty
	 */
	public function pop() {
		if(!$this->size)
			return null;
		$this->size--;
		return array_pop($this->items);
	}
	
	/**
	 * @return mixed  A copy of the item on top of the stack, or null if the stack is empty
	 */
	public function &top() {
		if($this->size)
			return $this->items[$this->size - 1];
		else
			return null;
	}
	
	/**
	 * @return mixed  A copy of the item on the bottom of the stack, or null if the stack is empty
	 */
	public function &bottom() {
		if($this->size)
			return $this->items[0];
		else
			return null;
	}
	
}
?>