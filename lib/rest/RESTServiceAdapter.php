<? # http://www.upcoming.org/services/rest/?api_key=<API Key>&method=event.search&search_text=killers&metro_id=1
/**
 * REST service client
 *
 * <b>Example</b>
 * <code>
 * $flickr = new RESTServiceAdapter('http://www.flickr.com/services/rest/');
 * echo $flickr->call('flickr.test.echo', 
 *   array('api_key' => '9b4439ce94de7e2ec2c2e6ffadc22bcf'));
 * </code>
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage rest
 */
class RESTServiceAdapter {
	
	/** @var  URL  Service endpoint url */
	protected $url = null;
	
	/**
	 * @param  mixed  <samp>URL</samp> or <samp>string</samp>
	 */
	public function __construct($url) {
		$this->url = URL::valueOf($url);
	}
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, ...])
	 * @return RESTResponse
	 */
	public function call($method, $arguments = array())
	{
		try {
			$request = $this->prepareRequest(new RESTRequest($method, $arguments));
			return new RESTResponse($method, $request->execute());
		}
		catch(PHPException $e) {
			throw new RESTException($e, 'Failed to call REST method');
		}
	}
	
	/**
	 * @param  RESTRequest
	 * @return RESTRequest
	 */
	protected function prepareRequest(RESTRequest $request) {
		$request->url = $this->url;
		$request->arguments['method'] = $request->method;
		return $request;
	}
}
?>