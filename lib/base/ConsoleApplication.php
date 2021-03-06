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
 * CLI (Command Line Interface) application
 *
 * <b>Work In Progress</b>
 * 
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage util
 */
abstract class ConsoleApplication {
	
	/** @var bool */
	public $silent = false;
	
	/** @var resource */
	public $stderr = null;
	
	/**
	 * @param  int    Number of arguments
	 * @param  array  Arguments
	 * @return void
	 */
	abstract public function main(&$argc, &$argv);
	
	/**
	 * New application
	 */
	public function __construct() {
		try {
			exit($this->main($GLOBALS['argc'], $GLOBALS['argv']));
		}
		catch(Exception $e) {
			print $GLOBALS['argv'][0] . ': ' . ABException::format($e, true, false);
			exit($e->getCode() ? $e->getCode() : 1);
		}
	}
	
	/**
	 * Will print $msg on a new line, unless $this->silent == true
	 *
	 * @param  string
	 * @param  bool    If true, the message will go to stderr instead of stdout
	 * @return void
	 */
	public function println($msg='', $to_stderr = false) {
		if(!$this->silent) {
			if($to_stderr) {
				if($this->stderr === null)
					$this->stderr = fopen('php://stderr', 'w');
				fwrite($this->stderr, $msg);
				fwrite($this->stderr, "\n",1);
			}
			else {
				echo $msg, "\n";
			}
		}
	}
	
	/**
	 * @param  string
	 * @param  int     if($exit > 0) exit($exit)
	 * @return void
	 */
	public function triggerError($message, $exit = 1) {
		$this->println($GLOBALS['argv'][0] . ': ' . rtrim($message), true);
		if($exit > 0)
			exit($exit);
	}
	
	
	/**
	 * A better getopt() function to return an array based on argv
	 * settings. does not modify $argv or $argc so can be parsed
	 * multiple times with multiple option array's.
	 * 
	 * written because php's getop function only allows for single
	 * character command line options (-a -f -c, etc) and i like
	 * long command line options so i can remeber what they do
	 * more clearly ;)
	 * 
	 * bugs: when using short input, if a long input exists with the
	 * same beginning, bgetop parses funny. ex:
	 * 
	 * options -f: and --foo are two seperate opAr values,
	 * run: ./getopt.php --append -f wot foobar
	 * and because --foo has no trailing ':', 'wot' is ignored, and f
	 * is set true. the 'foo' AND 'f' options are popped off the opAr.
	 * (because '-f' exists in both '-f' and -'-f'oo)
	 * 
	 * solution: none comes to mind.
	 * workaround: don't mix short and long variables on seperate
	 * options. -f and --bfoo works as expected. -i|--input does as well.
	 * 
	 * Example:
	 * <code>
	 * $op = $this->getopt(array(
	 * 		'-a|--append',   // a or append toggle, nothing extra
	 * 		'-i|--input:',   // i or input with next input being needed
	 * 		'-l|--list:',    // l with input needed
	 * 		'--foo',         // does not work - broken (FIXME)
	 * 		'-f:',           // f with input
	 * 		'--wot:'         // wot with input, no short
	 * ));
	 * </code>
	 *
	 * @param  array
	 * @param  bool
	 * @return array
	 */
	public function getopt($opAr, $remove = true)
	{
		$argv =& $GLOBALS['argv'];
		$argc =& $GLOBALS['argc'];
		$argPos = 1;
		$return = array();
		$new_argc = $argc;
		
		// foreach arg
		while ($argPos<$argc) {
			$arg = $argv[$argPos];
			if(!$arg) {
			  continue;
		  }
			if ($arg{0}=="-") {
				if ($arg{1}=="-") {
					$var = substr($arg,2,strlen($arg));
				} else { 
					$var = $arg{1};
				}
				foreach ($opAr as $opk => $opv) {
					if (!isset($return[$var])) {
						if (strpos($opv,$arg) !== FALSE) {
							// this is where the -f -foo fix needs to be,
							// the partial string exists in this record,
							// but we need to determine if it's accurate
							// somehow (i'm thinking: eregi?)
							if ($accurate=1) {
								// we foudn the key
								if (strpos($opv,':') !== FALSE) {
									// next value is the one to use,
									// then skip it in the parser.
									if (isset($argv[$argPos+1])) {
										$return[$var] = $argv[++$argPos];
										if($remove)
											unset($argv[$argPos-1]);
									} else {
										$return[$var] = FALSE;
									}
								} else {
									// just set the toggle
									$return[$var] = TRUE;
								}
								// don't check this opAr value again
								unset($opAr[$opk]);
							}
						} // if accurate
						if($remove) {
							isset($argv[$argPos]);
								unset($argv[$argPos]);
							#$new_argc--;
						}
					} // !isset already
				} // foreach
			}
			$argPos++;
		} // while argPos < argc
		
		if($remove) {
			$GLOBALS['argv'] = array_values($GLOBALS['argv']);
			$GLOBALS['argc'] = count($GLOBALS['argv']);
		}
		
		return $return;
	}
}
?>