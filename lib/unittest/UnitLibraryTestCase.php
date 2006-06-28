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
		$this->path = $path;
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
		$this->importClassFiles($this->path);
		
		# Aquire declared classes
		$classes = get_declared_classes();
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($classes as $class)
		{
			# Has __test method?
			if(is_callable(array($class, '__test'), false))
			{
				# Roots from this library path?
				$classInfo = new ReflectionClass($class);
				$declaredInFile = $classInfo->getFileName();
				if(substr($declaredInFile, 0, strlen($this->path)) == $this->path)
				{
					# Create and execute test
					$this->executeTest(new UnitClassTestCase($class, $classInfo));
				}
			}
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
				if(class_exists($guessedClass))
					continue;
				
				require_once $filepath;
			}
			elseif($this->recursive && is_dir($filepath) && is_readable($filepath))
			{
				# Make sure all classes are available for autoloading, in case
				# we import a sublcass before we import it's superclass
				if(!PHP::libraryIsLoaded($filepath))
					PHP::addClasspath($filepath);
				
				# Recurse down the alley...
				$this->importClassFiles($filepath);
			}
		}
	}
}
?>