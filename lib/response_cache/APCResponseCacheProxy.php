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
 * APC-based response caching.
 * 
 * <b>Example:</b><code>
 * &lt;?
 * require_once 'boot.php';
 * import('response_cache');
 * APCResponseCacheProxy::activate();
 * header('X-Time: '.time());
 * echo time();
 * ?&gt;
 * </code>
 * 
 * <b>Supported headers</b>
 *   - Expires: Adjusts TTL using ttl=expires-time(). If Expires is a date in
 *     the past or now it causes a cache miss.
 *   - Cache-control: If "max-age" is set, TTL will be adjusted to that value. 
 *     "max-age" supersedes "Expires" value if "Expires" is a date in the future.
 *     If "private" or "no-cache" is set, it causes a cache miss.
 *   - Pragma: Currently handled exactly like "Cache-control".
 *
 * Requires APC 3.0.0 (recomended version: >=3.0.9)
 * 
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage response_cache
 */
class APCResponseCacheProxy {
  
  /** @var int */
  public static $ttl = 3600;
  
  /**
   * Cache map key.
   * If not set when calling activate, Request::url() will be used.
   * @var string
   */
  public static $key = null;
  
  /**
   * Set to one of crc32, md5 or sha1 to enable ETag tagging of response body.
   * 
   * For servers with lots of cache misses, this may cause a significant 
   * performance hit as checksums need to be calculated.
   * 
   * Note: Only tagging of response body is implemented. Comparison in 
   *       conditionals like If-Match is not supported (it will have no 
   *       effect if requested).
   * 
   * @var string
   */
  public static $etagCheck = 'crc32';
  
  /** @var bool */
  public static $tidy = false;
  
  /** @var bool */
  protected static $activated = false;
  
  /** @var bool */
  protected static $finalized = false;
  
  
  /**
   * @param  bool
   * @return void
   * @throws IllegalStateException if already activated
   */
  public static function activate($force_cache_reload=false) {
    if(self::$activated) {
      throw new IllegalStateException('already activated');
    }
    self::$activated = true;
    if(!self::$key) {
      self::$key = Request::url();
    }
    if(!$force_cache_reload) {
      $body = apc_fetch(self::$key.'#B');
      if($body !== false) {
        if(($headers = apc_fetch(self::$key.'#H'))) {
          $num_headers = count($headers);
          for($i=0;$i<$num_headers;$i++) {
            header($headers[$i], true);
          }
        }
        exit($body);
      }
    }
    register_shutdown_function(array('APCResponseCacheProxy','finalize'));
    ob_start();
  }
  
  
  /**
   * Tells where there or not the proxy has been activated.
   * 
   * @return bool
   */
  public static function isActive() {
    return self::$activated;
  }
  
  
  /**
   * @return void
   */
  public static function finalize() {
    if(self::$finalized) {
      return;
    }
    if(connection_status() == CONNECTION_NORMAL) {
      $headers = headers_list();
      $custom_expire_date = false;
      $no_cache = false;
      $custom_max_age = false;
      $custom_etag = false;
      $now = time();
      
      # Check headers
      foreach($headers as $header) {
        $k='';
        $v='';
        $header = strtolower($header);
        if(($p = strpos($header, ':')) !== false) {
          $k = substr($header,0,$p);
          $v = substr($header,$p+1);
        } else {
          $k = $header;
        }
        # Look at cache-control header
        if(($k == 'cache-control') or ($k == 'pragma')) {
          if((strpos($v, 'no-cache') !== false) or (strpos($v, 'private') !== false)) {
            $no_cache = true;
            break; # We do not need to know anything else - abort and respond.
          }
          elseif( !$custom_max_age and ($p = strpos($v, 'max-age=')) !== false) {
            $p += 8;
            $max_age = null;
            if(($pp = strpos($v, ';', $p)) !== false) {
              $max_age = intval(substr($v, $p, $pp-$p));
            } else {
              $max_age = intval(substr($v, $p));
            }
            if($max_age < 1) {
              $no_cache = true;
            } else {
              $custom_max_age = true;
              self::$ttl = $max_age;
            }
          }
        }
        # Parse custom Expires-header
        elseif(!$custom_expire_date and $k == 'expires') {
          if(($t = strtotime($v)) !== false) {
            if($t < $now) {
              # Someone trying to force expiration
              $no_cache = true;
            } else {
              # Adjust TTL
              self::$ttl = $now-$t;
              if(self::$ttl < 1) {
                $no_cache = true;
              } else {
                $custom_expire_date = true;
              }
            }
          } else {
            error_log(get_class().'::finalize() failed to parse date in Expire header');
          }
        }
        elseif($k == 'etag') {
          $custom_etag = true;
        }
      }
      # Skip caching?
      if($no_cache) {
        ob_end_flush();
      }
      else {
        $body = ob_get_clean();
        
        # Apply tidy
        if(self::$tidy) {
            
            #'indent-spaces' => 0
            #'tab-size' => 0
          
          $tidy = new tidy;
          $tidy->parseString($body, array(
            'clean'=>1,
            'bare'=>1,
            'hide-comments'=>1,
            'doctype'=> 'omit',
            'indent-spaces'=>0,
            'tab-size'=>0,
            'wrap'=>0,
            'quote-ampersand'=>0,
            #'indent'         => true,
            'output-xhtml'   => true,
            'quiet' => 1
          ), 'utf8');
          $tidy->cleanRepair();
          $body = tidy_get_output($tidy);
        }
        
        # Add expires header if not exists
        if(!$custom_expire_date) {
          $headers[] = 'Expires: '.gmdate('r', time()+self::$ttl);
        }
        
        # Add ETag header
        if(self::$etagCheck and !$custom_etag) {
          $checksum_func = &self::$etagCheck;
          if($checksum_func == 'crc32') {
            $etag = sprintf('ETag: "%x"', crc32($body));
          } else {
            $etag = 'ETag: "'.$checksum_func($body).'"';
          }
          $headers[] = $etag;
          header($etag);
        }
        
        $headers[] = 'Content-Length: '.strlen($body);
        $headers[] = 'X-Cache: HIT from '.php_uname('n');
        #$headers[] = 'Via: '.get_class().'/r'.$rev;
        apc_store(self::$key.'#H', $headers, self::$ttl);
        apc_store(self::$key.'#B', $body, self::$ttl);
        header('Content-Length: '.strlen($body));
        header('X-Cache: MISS from '.php_uname('n'));
        echo $body;
      }
    }
    else {
      # Client abort/disconnected or error
      # connection_status() != CONNECTION_NORMAL
      error_log(get_class().'::finalize(): skipped response because of client error');
      ob_end_clean();
    }
    self::$finalized = true;
  }
}
?>