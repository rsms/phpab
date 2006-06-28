<?
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