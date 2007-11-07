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
 * Filters to be used together with APCResponseCacheProxy
 * 
 * @version    $Id$
 * @author     Rasmus Andersson  http://hunch.se/
 * @package    ab
 * @subpackage response_cache
 */
class ResponseFilters {
  /**
   * Filter through Tidy
   * 
   * @param  array
   * @param  string
   * @param  bool
   * @return bool
   */
  public static function tidy(array &$headers, &$body, $uncached) {
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
      'output-xhtml'   => true,
      'quiet' => 1
    ), 'utf8');
    $tidy->cleanRepair();
    $body = tidy_get_output($tidy);
  }
}