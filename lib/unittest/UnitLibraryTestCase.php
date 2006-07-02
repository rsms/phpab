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
		$this->importLibrary($this->path);
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
		if(substr($path,-2) != '.d')
			import($path);
		
		foreach(File::valueOf($path)->getFiles(true) as $file)
			if($this->recursive && $file->isDir() && $file->isReadable())
				$this->importLibrary($file->getPath());
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
				if(!class_exists($guessedClass) && !interface_exists($guessedClass))
					die('FATAL: Unthrowable error: '.__FILE__.':'.(__LINE__-1)
						." Unable to find probable class $guessedClass");
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