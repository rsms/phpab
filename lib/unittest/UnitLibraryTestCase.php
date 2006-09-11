<?
/**
 * Perform tests on all classes in a library
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitLibraryTestCase extends UnitDirectoryTestCase {
	
	/** @var SimpleLogger */
	public static $log = null;
	
	/**
	 * @param string
	 * @param bool
	 */
	public function __construct($path, $recursive = true) {
		$this->path = realpath($path);
		$this->recursive = $recursive;
	}
	
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests()
	{
		# Import all class definitions
		if($this->log)
			$this->log->info("Importing libraries...\n");
		$this->importLibrary($this->path);
		if($this->log)
			$this->log->info("Importing classes and interfaces...\n");
		$this->importClassFiles($this->path);
		
		# Aquire declared classes
		$classes = get_declared_classes();
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($classes as $class)
		{
			# Has __test method?
			if(is_callable(array($class, '__test'), false))
			{
				$classInfo = new ReflectionClass($class);
				
				# Roots from this library path?
				$declaredInFile = $classInfo->getFileName();
				if(substr($declaredInFile, 0, strlen($this->path)) == $this->path)
				{
				
					# Only include __test()s explicitly defined in sublcasses
					$hasItsOwnTest = true;
					
					if($classInfo->getParentClass()) {
						foreach($classInfo->getMethods() as $method) {
							if($method->getName() == '__test' && $method->getDeclaringClass() != $classInfo) {
								$hasItsOwnTest = false;
								break;
							}
						}
					}
				
					# Create and execute test
					if($hasItsOwnTest)
						$this->executeTest(new UnitClassTestCase($class, $classInfo));
				}
			}
		}
	}
	
	
	/**
	 * @param  string
	 * @return void
	 */
	protected function importLibrary($path)
	{
		$this->_importLibrary($path);
		
		foreach(File::valueOf($path)->getFiles(true) as $file)
			if($this->recursive && $file->isDir() && $file->isReadable())
				$this->_importLibrary($file->getPath());
	}
	
	
	/* Smisk */
	private function _importLibrary($path)
	{
		if(substr($path,-2) != '.d' && $path{0} != '.') {
			if($this->log)
				$this->log->debug("Importing library $path ...".substr($path,-2));
			import($path);
			if($this->log)
				$this->log->debug("OK\n");
		}
	}
	
	
	/**
	 * @param  string
	 * @return void
	 */
	protected function importClassFiles($path)
	{
		foreach(scandir($path) as $file)
		{
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			if(strrchr($file, '.') == '.php')
			{
				if(!preg_match('/^[A-Z]/', $file))
					continue;
				
				$guessedClass = substr($file, 0, -4);
				
				if($this->log)
					$this->log->debug("Loading class $guessedClass from ".basename($path).'/'.basename($file)." ... ");
				
				if(!class_exists($guessedClass) && !interface_exists($guessedClass, false)) {
					die('FATAL: Unthrowable error: '.__FILE__.':'.(__LINE__-1)
						." Unable to find probable class or interface $guessedClass");
				}
				
				if($this->log)
					$this->log->debug("OK\n");
			}
			elseif($this->recursive && is_dir($filepath) && is_readable($filepath))
			{
				# Recurse down the alley...
				$this->importClassFiles($filepath);
			}
		}
	}
}
?>