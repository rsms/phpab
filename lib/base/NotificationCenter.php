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
 * The NotificationCenter class provides a way to send notifications to objects 
 * in the same process. It takes a notification name and optionally a payload, and 
 * broadcasts them to any objects in the same process that have registered to 
 * receive the notification with the task's default notification center.
 *
 * <b>Example:</b>
 * <code>
 * class ToyFactory {
 * 	public $id;
 * 	public function __construct($id) {
 * 		$this->id = $id;
 * 	}
 * 	public function makeNewToy($type) {
 * 		print "ToyFactory {$this->id}: I'm making a new toy of type $type...\n";
 * 	}
 * 	public function closeFactory($n) {
 * 		print "ToyFactory {$this->id}: Closing factory...\n";
 * 	}
 * }
 * 
 * $t1 = new ToyFactory(1);
 * $t2 = new ToyFactory(2);
 * 
 * $nc->addObserver($t1, 'makeNewToy');
 * $nc->addObserver($t2, 'makeNewToy');
 * $nc->post('makeNewToy', 'Car');
 * # >> ToyFactory 1: I'm making a new toy of type Car...
 * # >> ToyFactory 2: I'm making a new toy of type Car...
 * $nc->removeObserver($t2, 'makeNewToy');
 * $nc->post('makeNewToy', 'Clown');
 * # >> ToyFactory 1: I'm making a new toy of type Clown...
 * $nc->addObserver($t2, 'closeFactory');
 * $nc->post('closeFactory');
 * # >> ToyFactory 2: Closing factory...
 * $nc->addObserver($t2, 'killAllChildren');
 * # >> Uncaught exception 'Exception' with message 'Observer does not have a killAllChildren notification method'
 * </code>
 *
 * @version    $Id$
 * @author     Rasmus Andersson {@link http://hunch.se/}
 * @author     Fredrik Stark {@link http://altnet.se/}
 * @package    ab
 * @subpackage util
 */
class NotificationCenter {
	
	/** @var NotificationCenter */
	private static $defaultCenter;
	
	/** @var object[] */
	public $observers = array();
	
	/**
	 * Returns the process’s default notification center.
	 * 
	 * @return NotificationCenter
	 */
	public static function defaultCenter() {
		if(!self::$defaultCenter)
			self::$defaultCenter = new self;
		return self::$defaultCenter;
	}

	/**
	 * Add an observer
	 *
	 * @param  object
	 * @param  string
	 * @return void
	 * @throws IllegalArgumentException  if the observer does not implement the named receiver method.
	 */
	public function addObserver( $o, $forNotification )
	{
		if(!method_exists($o, $forNotification))
			throw new IllegalArgumentException('Observer does not have a '.$forNotification.' receiver method');
		if(!isset($this->observers[$forNotification]))
			$this->observers[$forNotification] = array($o);
		else
			$this->observers[$forNotification][] = $o;
	}

	/**
	 * Remove an observer from all or a specific notification
	 *
	 * @param  object
	 * @param  string
	 * @return bool   Removed or not
	 */
	public function removeObserver( $o, $forNotification=null )
	{
		if($forNotification !== null) {
			if(isset($this->observers[$forNotification])) {
				$a =& $this->observers[$forNotification];
				if(($i = array_search($o, $a)) !== false) {
					unset($a[$i]);
					if(!$a)
						unset($this->observers[$forNotification]);
					return true;
				}
			}
		}
		else {
			foreach($this->observers as $n => $a) {
				if(($i = array_search($o, $a)) !== false) {
					unset($this->observers[$n][$i]);
					if(!$this->observers[$n])
						unset($this->observers[$n]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Post a notification
	 *
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function post( $notification, $arg=null )
	{
		if(isset($this->observers[$notification]))
			foreach($this->observers[$notification] as $o)
				$o->$notification($arg);
	}
}

?>