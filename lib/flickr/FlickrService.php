<?
/**
 * Flickr service
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage flickr
 */
class FlickrService extends RESTServiceAdapter {
	
	/**
	 * A reference to the last instantiated FlickrService object.
	 * 
	 * <b>Example:</b>
	 * <code>
	 * $flickr = new FlickrService('mykey');
	 * ...
	 * FlickrService::$instance->call('some.method');
	 * </code>
	 * 
	 * @var FlickrService
	 */
	public static $instance = null;
	
	/** @var string */
	public $api_key = '';
	
	/**
	 * @param  string
	 */
	public function __construct($api_key)
	{
		$this->api_key = $api_key;
		iconv_set_encoding('output_encoding', 'UTF-8');
		iconv_set_encoding('input_encoding', 'UTF-8');
		iconv_set_encoding('internal_encoding', 'UTF-8');
		$this->url = new URL('http://www.flickr.com/services/rest/');
		self::$instance = $this;
	}
	
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, string key => string value[, ...]])
	 * @return RESTResponse
	 */
	public function call($method, $arguments = array())
	{
		$res = parent::call($method, $arguments);
		if($res->getStatus() == 'fail')
			throw new RESTException($res->dom['err'][0]['@']['msg'], intval($res->dom['err'][0]['@']['code']));
		return $res;
	}
	
	
	/**
	 * @param  RESTRequest
	 * @return RESTRequest
	 */
	protected function prepareRequest(RESTRequest $request)
	{
		$request->url = $this->url;
		$request->arguments['method'] = $request->method;
		$request->arguments['api_key'] = $this->api_key;
		return $request;
	}
}
?>