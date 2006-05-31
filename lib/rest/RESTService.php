<?
/**
 * REST service client
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage rest
 */
class RESTServiceClient {
	
	/** @var URL Service endpoint url */
	protected $url = null;
	
	/**
	 * @param  string
	 * @param  array  (string key => string value[, string key => string value[, ...]])
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