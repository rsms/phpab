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
 * @package    ab
 * @subpackage rest
 */
class RESTService {
  
  /** @var bool */
  public $debug = false;
	
	/**
	 * @param  string
	 * @param  array (string key => mixed value, ...)
	 * @param  string  "POST"|"GET"
	 * @return array XML DOM
	 * @throws HTTPException
	 * @throws XMLParserException
	 */
	public function call($url, $params=null, $method='GET')
	{
		if($params && is_array($params)) {
			$vv = array();
			foreach($params as $k => $v) {
				if (is_array($v)) {
					foreach($v as $v2) {
						$vv[] = rawurlencode($k).'='.rawurlencode($v2);
					}
				} else {
					$vv[] = rawurlencode($k).'='.rawurlencode($v);
				}
			}
			$params = implode('&',$vv);
		} else {
			$params = '';
		}
		
		$conn = new HTTPConnection(strval($url));
		$conn->method = $method;
		$conn->curlOptions[CURLOPT_USERAGENT] = 'RESTService $Id$';
		$conn->curlOptions[CURLOPT_CONNECTTIMEOUT] = 10; # short connect timeout
		
		if($method != 'POST' && $params) {
			$conn->url .= '?'.$params;
			$params = null;
		}
		
		if($this->debug) {
			print 'DEBUG>> RESTService->call('.$service_id.':'.$name.'): '.(($conn->method == 'POST') ? 'POST':'GET').' '.$conn->url."<br/>\n";
			if($conn->method == 'POST')
				print 'DEBUG>> POST data: '.var_export($params,1)."\n<br/>";
		}
		
		# Send request
		$data = $conn->connect($params);
		
		if($this->debug) {
			print 'DEBUG>> RESTService->call('.$service_id.':'.$name.'): Response: HTTP/'
				. $conn->responseProtocol.' '.$conn->responseStatus."<br/>\n$data\n<br/>";
		}
		
		# Parse response
		$data = XML::loadString($data);
		
		# Handle response errors
		if(substr(strval($conn->responseStatus),0,1) != '2') {
			throw new HTTPException($conn->responseStatusName, $conn->responseStatus);
		}
		
		return $data;
	}
}
?>