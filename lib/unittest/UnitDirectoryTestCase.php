<?
/**
 * Perform tests on all php-files in a directory
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitDirectoryTestCase extends UnitTestCase {
	
	/** @var string */
	protected $path = '';
	
	/** @var bool */
	protected $recursive = false;
	
	/** @var UnitTestCase[] */
	protected $cases = array();
	
	
	/**
	 * @param string
	 * @param bool
	 */
	public function __construct($path, $recursive = false) {
		$this->path = $path;
		$this->recursive = $recursive;
	}
	
	/**
	 * @return UnitTestCase[]
	 */
	public function getCompletedTestCases() {
		return $this->cases;
	}
	
	/**
	 * Run tests
	 *
	 * @return void
	 */
	public function test()
	{
		# Refresh assert bindings
		UnitTest::instance()->refreshAssertBindings();
		
		# Reset case storage & passed-cache
		$this->cases = array();
		$this->allCasesPassed = true;
		
		# Implementation-specific test logic
		$this->performTests();
	}
	
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests()
	{
		# Import all php-files
		$files = array();
		$this->findPHPFiles($this->path, $files);
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($files as $file)
		{
			# Create and execute test
			$this->executeTest(new UnitFileTestCase($files));
		}
	}
	
	/**
	 * @param  string
	 * @param  array
	 * @return void
	 */
	protected function findPHPFiles($path, &$files)
	{
		foreach(scandir($path) as $file)
		{
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			
			if(strrchr($file, '.') == '.php')
			{
				$files[] = $filepath;
			}
			elseif($this->recursive && is_dir($filepath) && is_readable($filepath))
			{
				# Recurse down the alley...
				$this->findPHPFiles($filepath, $files);
			}
		}
	}
	
	/**
	 * Execute one subcase
	 *
	 * Associates the test with this case, then runs it and finally updates 
	 * $this->allCasesPassed depending on the result of $case->passed()
	 * 
	 * @param  UnitTestCase
	 * @return void
	 */
	protected function executeTest(UnitTestCase $case)
	{
		$this->cases[] = $case;
		$case->test();
		
		if($this->allCasesPassed && !$case->passed())
			$this->allCasesPassed = false;
	}
	
	
	/** @return bool */
	public function passed() {
		return $this->allCasesPassed;
	}
}
?>