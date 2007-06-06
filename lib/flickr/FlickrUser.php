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
 * A Flickr user
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage flickr
 */
class FlickrUser {
	
	protected static $instance_cache = array();
	
	public $id = '';
	public $username = null;
	public $realname = null;
	
	/** @var bool */
	public $is_admin = 0;
	
	/** @var bool */
	public $is_pro = 0;
	
	/** @var string */
	public $photos_url = '';
	
	/** @var string */
	public $profile_url = '';
	
	
	/** @var bool */
	private $has_loaded_info = false;
	
	/**
	 * @param  string
	 * @param  string
	 */
	public function __construct($id, $username = null) {
		$this->id = $id;
		$this->username = $username;
		$this->realname = $username;
		self::$instance_cache[$id] = $this;
	}
	
	
	/**
	 * @param  string
	 * @return FlickrUser
	 */
	public static function findByUsername($username) {
		return self::valueOf(FlickrService::$instance->call('flickr.people.findByUsername', array('username' => $username)));
	}
	
	
	/**
	 * @param  string
	 * @return FlickrUser
	 */
	public static function findById($id) {
		return isset(self::$instance_cache[$id]) ? self::$instance_cache[$id] : self::valueOf($id);
	}
	
	/** @return string */
	public function getPhotosURL() {
		if(!$this->photos_url)
			$this->loadInfo();
		return $this->photos_url;
	}
	
	
	/**
	 * @param  int     Number of photos to return per page. If this argument is omitted, it defaults to 100. 
	 *                 The maximum allowed value is 500.
	 *
	 * @param  int     The page of results to return. If this argument is omitted, it defaults to 1.
	 *
	 * @param  string  A comma-delimited list of extra information to fetch for each returned record. 
	 *                 Currently supported fields are: license, date_upload, date_taken, owner_name, 
	 *                 icon_server, original_format, last_update.
	 *
	 * @return FlickrPhoto[]
	 */
	public function getPublicPhotos($per_page = 100, $page = 1) {
		$res = FlickrService::$instance->call('flickr.people.getPublicPhotos', array(
			'user_id' => $this->id, 'per_page' => $per_page, 'page' => $page, 'extras' => FlickrPhoto::$std_extras));
		
		return FlickrPhoto::valueOfCollection($res->dom['photos'][0]['photo']);
	}
	
	/**
	 * @param  mixed
	 * @return FlickrUser
	 */
	public static function valueOf($v) {
		if(is_object($v)) {
			if($v instanceof RESTResponse)
				return self::valueOf($v->dom['user'][0]);
		}
		elseif(is_array($v)) {
			$user = new FlickrUser($v['@']['nsid'], isset($v['username'][0]['#']) ? $v['username'][0]['#'] : @$v['@']['username']);
			if(isset($v['@']['realname']))
				$user->realname = $v['@']['realname'];
			return $user;
		}
		else {
			return new FlickrUser($v);
		}
		return null;
	}
	
	
	/** @return void */
	public function loadInfo()
	{
		if($this->has_loaded_info)
			return;
		
		$res = FlickrService::$instance->call('flickr.people.getInfo', array('user_id' => $this->id));
		
		$n =& $res->dom['person'][0];
		$a =& $n['@'];
		
		$this->is_admin = intval($a['isadmin']);
		$this->is_pro = intval($a['ispro']);
		
		$this->username = $n['username'][0]['#'];
		$this->realname = $n['realname'][0]['#'];
		
		$this->photos_url = $n['photosurl'][0]['#'];
		$this->profile_url = $n['profileurl'][0]['#'];
		
		$this->original_format = $a['originalformat'];
		
		/*
		<person nsid="12037949754@N01" isadmin="0" ispro="0" iconserver="3">
			<username>bees</username>
			<realname>Cal Henderson</realname>
				<mbox_sha1sum>eea6cd28e3d0003ab51b0058a684d94980b727ac</mbox_sha1sum>
			<location>Vancouver, Canada</location>
			<photosurl>http://www.flickr.com/photos/bees/</photosurl> 
			<profileurl>http://www.flickr.com/people/bees/</profileurl> 
			<photos>
				<firstdate>1071510391</firstdate>
				<firstdatetaken>1900-09-02 09:11:24</firstdatetaken>
				<count>449</count>
			</photos>
		</person>
		*/
		
		$this->has_loaded_info = true;
	}
}
?>