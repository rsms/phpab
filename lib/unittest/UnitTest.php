<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitTest {
	
	/** @var UnitTest */
	protected static $instance = null;
	
	/** @var UnitTestAssertion[] */
	protected $assertions = array();
	
	
	/** @return UnitTest */
	public static function instance() {
		if(!self::$instance)
			self::$instance = new self;
		return self::$instance;
	}
	
	/** Singleton */
	protected function __construct() {
		$this->refreshAssertBindings();
	}
	
	/**
	 * @param  string
	 * @param  int
	 * @param  string
	 */
	public function assertCallback( $file, $line, $message )
	{
		$message = trim($message);
		$this->assertions[] = new UnitTestAssertion($line, $message ? ereg_replace('^.*//\*', '', $message) : '');
	}
	
	/** @return void */
	public function refreshAssertBindings() {
		assert_options(ASSERT_CALLBACK, array($this, 'assertCallback'));
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		assert_options(ASSERT_BAIL, 0);
	}
	
	/**
	 * @return array
	 */
	public function &getAssertionStorage() {
		return $this->assertions;
	}
	
	/**
	 * @param  array
	 * @return void
	 */
	public function setAssertionStorage(&$storage) {
		$this->assertions =& $storage;
	}
	
	/**
	 * Run tests on all available php classes in dir $path.
	 *
	 * @param  string
	 * @param  bool    Include subdirectories
	 * @return UnitClassTestCase[]
	 */
	public function testLibrary($path, $recursive = false)
	{
		$this->refreshAssertBindings();
		$classes = get_declared_classes();
		$this->importFilesInDir($path, $recursive, '/^[A-Z]/', true);
		
		# find out which classes were loaded
		$classes = array_diff(get_declared_classes(), $classes);
		
		# test them!
		$cases = array();
		
		foreach($classes as $class) {
			if(is_callable(array($class, '__test'), false))
			{
				$case = new UnitClassTestCase($class);
				$case->test($this);
				$cases[] = $case;
			}
		}
		
		return $cases;
	}
	
	/** @return void */
	private function importFilesInDir($path, $recursive, $filenameFilterRE = null, $checkClasses = false)
	{
		foreach(scandir($path) as $file)
		{
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			if(strrchr($file, '.') == '.php')
			{
				if($filenameFilterRE && !preg_match($filenameFilterRE, $file))
					continue;
				
				if($checkClasses) {
					$guessedClass = substr($file, 0, -4);
					if(class_exists($guessedClass)) {
						continue;
					}
				}
				
				require_once $filepath;
			}
			elseif($recursive && is_dir($filepath) && is_readable($filepath)) {
				PHP::addClasspath($filepath);
				$this->importFilesInDir($filepath, $recursive, $filenameFilterRE, $checkClasses);
			}
		}
	}
}
?>