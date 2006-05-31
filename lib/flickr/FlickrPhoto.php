<?
class FlickrPhoto {
	
	/** @var FlickrPhoto[] */
	protected static $instance_cache = array();
	
	
	# Size constants, used by getURL()
	
	/** @var string 75x75 */
	public static $SIZE_SMALL_SQUARE = 's';
	
	/** @var string 100 on longest side */
	public static $SIZE_THUMBNAIL = 't';
	
	/** @var string 240 on longest side */
	public static $SIZE_SMALL = 'm';
	
	/** @var string 500 on longest side */
	public static $SIZE_MEDIUM = '';
	
	/** @var string 1024 on longest side (only exists for high-res photos) */
	public static $SIZE_LARGE = 'b';
	
	/** @var string original size */
	public static $SIZE_ORIGINAL = 'o';
	
	/** @ignore */
	public static $std_extras = 'date_upload,date_taken,original_format,last_update';
	
	
	# properties
	
	/** @var string */
	public $id = '';
	
	/** @var FlickrUser */
	public $owner = null;
	
	/** @var string */
	public $secret = '';
	
	/** @var int */
	public $server = 0;
	
	/** @var string */
	public $title = '';
	
	/** @var string */
	public $description = '';
	
	/** @var bool */
	public $is_public = 1;
	
	/** @var bool */
	public $is_friend = 0;
	
	/** @var bool */
	public $is_family = 0;
	
	/** @var bool */
	public $is_primary_in_set = 0;
	
	/** @var int timestamp */
	public $date_uploaded = 0;
	
	/** @var int timestamp */
	public $date_taken = 0;
	
	/** @var int timestamp */
	public $date_last_updated = 0;
	
	/** @var string */
	public $original_format = 'jpg';
	
	/** @var string */
	public $photopage_url = '';
	
	/** @var int */
	public $comment_count = -1;
	
	
	private $has_loaded_info = false;
	
	
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
		return isset(self::$instance_cache[$id]) ? self::$instance_cache[$id] : new FlickrPhoto($id);
	}
	
	
	/**
	 * @param  string  Size. You may use the <samp>FlickrPhoto::$SIZE_</samp> constants.
	 */
	public function getURL($size = '')
	{
		if(!$this->server || !$this->secret)
			$this->loadInfo();
		
		$ext = '';
		if($size)
			$ext = '_' . $size;
		
		if($size == 'o')
			$ext .= '.'.$photo->original_format;
		else
			$ext .= '.jpg';
		
		return new URL('http://static.flickr.com/'.$this->server.'/'.$this->id.'_'.$this->secret.$ext);
	}
	
	
	/**
	 * @param  array
	 * @return FlickrPhoto[]
	 */
	public static function valueOfCollection(&$a) {
		$photo_count = count($a);
		$photos = array();
		
		for($i=0;$i<$photo_count;$i++)
			$photos[] = FlickrPhoto::valueOf($a[$i]);
		
		return $photos;
	}
	
	
	/**
	 * @param  mixed
	 * @return FlickrPhoto
	 */
	public static function valueOf($v) {
		if(is_array($v)) {
			if(isset($v['@']) && isset($v['@']['id']) && $v['@']['id'] != '0')
			{
				$a =& $v['@'];
				
				$photo = new FlickrPhoto($a['id']);
				
				if(isset($a['owner']))
					$photo->owner = FlickrUser::findById($a['owner']);
				
				$photo->secret = @$a['secret'];
				$photo->server = @$a['server'];
				$photo->title = @$a['title'];
				$photo->is_public = @intval($a['ispublic']);
				$photo->is_friend = @intval($a['isfriend']);
				$photo->is_family = @intval($a['isfamily']);
				$photo->is_primary_in_set = @intval($a['isprimary']);
				
				$photo->date_uploaded = @intval($a['dateupload']);
				$photo->date_taken = @strtotime($a['datetaken']);
				$photo->date_last_updated = @intval($a['lastupdate']);
				$photo->original_format = isset($a['originalformat']) ? $a['originalformat'] : 'jpg';
				
				return $photo;
			}
		}
		else {
			return new FlickrPhoto(strval($v));
		}
		return null;
	}
	
	
	/** @return string */
	public function getTitle() {
		if(!$this->title)
			$this->loadInfo();
		return $this->title;
	}
	
	
	/** @return string */
	public function getPhotopageURL() {
		if(!$this->photopage_url)
			$this->loadInfo();
		return $this->photopage_url;
	}
	
	
	/** @return string */
	public function getCommentCount() {
		if($this->comment_count == -1)
			$this->loadInfo();
		return $this->comment_count;
	}
	
	
	/** @return void */
	public function loadInfo()
	{
		if($this->has_loaded_info)
			return;
		
		$res = FlickrService::$instance->call('flickr.photos.getInfo', array('photo_id' => $this->id));
		
		$n =& $res->dom['photo'][0];
		$a =& $n['@'];
		
		$this->secret = $a['secret'];
		$this->server = $a['server'];
		$this->original_format = $a['originalformat'];
		
		$this->title = @$n['title'][0]['#'];
		$this->description = @$n['description'][0]['#'];
		$this->owner = FlickrUser::valueOf($n['owner'][0]);
		
		if(isset($n['visibility'])) {
			$this->is_public = intval($n['visibility'][0]['@']['ispublic']);
			$this->is_friend = intval($n['visibility'][0]['@']['isfriend']);
			$this->is_family = intval($n['visibility'][0]['@']['isfamily']);
		}
		
		if(isset($n['dates'])) {
			$this->date_uploaded = intval($n['dates'][0]['@']['posted']);
			$this->date_taken = strtotime($n['dates'][0]['@']['taken']);
			$this->date_last_updated = intval($n['dates'][0]['@']['lastupdate']);
		}
		
		foreach($n['urls'][0]['url'] as $url) {
			if($url['@']['type'] == 'photopage')
				$this->photopage_url = $url['#'];
		}
		
		$this->comment_count = intval($n['comments'][0]['#']);
		
		/*
		<photo id="2733" secret="123456" server="12" isfavorite="0" license="3" rotation="90" originalformat="png">
			<owner nsid="12037949754@N01" username="Bees" realname="Cal Henderson" location="Bedford, UK" />
			<title>orford_castle_taster</title>
			<description>hello!</description>
			<visibility ispublic="1" isfriend="0" isfamily="0" />
			<dates posted="1100897479" taken="2004-11-19 12:51:19" takengranularity="0" lastupdate="1093022469" />
			<permissions permcomment="3" permaddmeta="2" />
			<editability cancomment="1" canaddmeta="1" />
			<comments>1</comments>
			<notes>
				<note id="313" author="12037949754@N01" authorname="Bees" x="10" y="10" w="50" h="50">foo</note>
			</notes>
			<tags>
				<tag id="1234" author="12037949754@N01" raw="woo yay">wooyay</tag>
				<tag id="1235" author="12037949754@N01" raw="hoopla">hoopla</tag>
			</tags>
			<urls>
				<url type="photopage">http://www.flickr.com/photos/bees/2733/</url> 
			</urls>
		</photo>
		*/
		
		$this->has_loaded_info = true;
	}
}
?>