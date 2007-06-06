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
 * A Flickr photo set
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage flickr
 */
class FlickrPhotoSet {
	
	/** @var FlickrPhotoSet[] */
	protected static $instance_cache = array();
	
	/** @var string */
	public $id = '';
	
	/** @var string */
	public $title = '';
	
	/** @var FlickrUser */
	public $owner = null;
	
	/** @var FlickrPhoto */
	public $primary = null;
	
	/** @var string */
	public $secret = '';
	
	/** @var string */
	public $server = '';
	
	/** @var int */
	public $photo_count = 0;
	
	/** @var string */
	public $description = '';
	
	
	/** @param string */
	public function __construct($id) {
		$this->id = $id;
		self::$instance_cache[$id] = $this;
	}
	
	
	/**
	 * @param  string
	 * @return FlickrUser
	 */
	public static function findById($id) {
		if(!isset(self::$instance_cache[$id]))
			self::$instance_cache[$id] = new FlickrPhotoSet($id);
		return self::$instance_cache[$id];
	}
	
	
	/**
	 * @param  int  Return photos only matching a certain privacy level. This only applies when making 
	 *              an authenticated call to view a photoset you own. Valid values are:
	 *              1 public photos
	 *              2 private photos visible to friends
	 *              3 private photos visible to family
	 *              4 private photos visible to friends & family
	 *              5 completely private photos
	 * 
	 * @return FlickrPhoto[]
	 */
	public function getPhotos($privacy_filter = 1)
	{
		$res = FlickrService::$instance->call('flickr.photosets.getPhotos', array(
			'photoset_id' => $this->id, 'privacy_filter' => $privacy_filter, 'extras' => FlickrPhoto::$std_extras));
		
		return FlickrPhoto::valueOfCollection($res->dom['photoset'][0]['photo']);
	}
	
	
	protected $context_cache = array();
	
	
	/**
	 * @param  string  photo-id
	 * @return FlickrPhoto[]  key 'prev' = previous photo, key 'next' = next photo
	 */
	public function getContext($photo_id)
	{
		if(!isset($this->context_cache[$photo_id]))
		{
			$res = FlickrService::$instance->call('flickr.photosets.getContext', array(
				'photoset_id' => $this->id, 'photo_id' => $photo_id, 'extras' => FlickrPhoto::$std_extras));
			
			$ctx = array('prev' => FlickrPhoto::valueOf($res->dom['prevphoto'][0]), 
			             'next' => FlickrPhoto::valueOf($res->dom['nextphoto'][0]));
			$this->context_cache[$photo_id] =& $ctx;
			return $ctx;
		}
		else {
			return $this->context_cache[$photo_id];
		}
	}
	
	
	/** @return string */
	public function getTitle() {
		if(!$this->title)
			$this->loadInfo();
		return $this->title;
	}
	
	
	/** @return string */
	public function getDescription() {
		if(!$this->description)
			$this->loadInfo();
		return $this->description;
	}
	
	
	/** @return void */
	protected function loadInfo() {
		$res = FlickrService::$instance->call('flickr.photosets.getInfo', array('photoset_id' => $this->id));
		
		$n =& $res->dom['photoset'][0];
		$a =& $n['@'];
		
		$this->owner = FlickrUser::findById($a['owner']);
		$this->primary = FlickrPhoto::findById($a['primary']);
		$this->secret = $a['secret'];
		$this->server = $a['server'];
		$this->photo_count = $a['photos'];
		$this->title = $n['title'][0]['#'];
		$this->description = $n['description'][0]['#'];
	}
	
}
?>