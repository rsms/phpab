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
 * Represents the current HTTP request.
 * 
 * Mostly based on information from request headers and server info provided
 * through SERVER_-vars.
 *
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage util
 */
class Request {
  /** @var boolean https or not */
  public static $secure;
  
  /** @var string '1.0' or '1.1' */
  public static $http_version;
  
  /** @var string */
  public static $host;
  
  /** @var int */
  public static $port = 80;
  
  /** @var string */
  public static $path;
  
  /** @var string */
  public static $query;
  
  /** @var string */
  public static $method;
  
  /** @var int  0 if not keepalive */
  public static $keepalive;
  
  /**
   * Complete request URL
   * 
   * @param  bool
   * @param  bool
   * @param  bool
   * @return string
   */
  public static function url($include_query=true, $include_path=true, $include_host=true) {
    return (self::$secure ? 'https://':'http://')
      . ($include_host ? self::$host
        . (((self::$secure and self::$port == 443) or (!self::$secure and self::$port == 80)) ? '':':'.self::$port)
        . ($include_path ? self::$path
          . (($include_query and self::$query) ? '?'.self::$query
          :'')
        :'')
      :'');
  }
}

# Initialized at load-time:

Request::$secure = isset($_SERVER['HTTPS']) and ($_SERVER['HTTPS'] == 'on');
Request::$http_version = isset($_SERVER['SERVER_PROTOCOL']) ? substr(strstr($_SERVER['SERVER_PROTOCOL'],'/'),1) : '1.0';
Request::$host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] :
  (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
if(($p = strpos(Request::$host,':')) !== false) {
  Request::$port = intval(substr(Request::$host, $p+1));
  Request::$host = substr(Request::$host, 0, $p);
} elseif(isset($_SERVER['SERVER_PORT'])) {
  Request::$port = intval($_SERVER['SERVER_PORT']);
}
Request::$query = @$_SERVER['QUERY_STRING'];
Request::$path = Request::$query ? substr(@$_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'],'?')) : rtrim(@$_SERVER['REQUEST_URI'],'?');
Request::$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
Request::$keepalive = isset($_SERVER['HTTP_KEEP_ALIVE']) ? intval($_SERVER['HTTP_KEEP_ALIVE']) : 0;
?>