<?
/**
 * Perform tests on all classes in a library
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage unittest
 */
class UnitLibraryTestCase extends UnitDirectoryTestCase
{
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
			$this->log->warn("Importing libraries...\n");
		$this->importLibrary($this->path);
		if($this->log)
			$this->log->warn("Importing classes...\n");
		$this->importClassFiles($this->path);
		
		# Aquire declared classes
		$classes = get_declared_classes();
		#var_dump($classes);
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($classes as $class)
		{
			# Has __test method?
			if(is_callable(array($class, '__test'), false))
			{
				$classInfo = new ABReflectionClass($class);
				
				# Disabled this because symlinked libraries might not root in the same
				# directory as we began looking in. The above test should be enough.
				#$declaredInFile = $classInfo->getFileName();
				#if(substr($declaredInFile, 0, strlen($this->path)) == $this->path)
			
				$hasItsOwnTest = true;
				
				# Only include __test()'s explicitly defined in subclasses
				if($classInfo->getParentClass())
				{
					$hasItsOwnTest = false;
					$classInfoName = $classInfo->getName();
					
					foreach($classInfo->getMethods() as $method)
					{
						if($method->getName() == '__test' && $method->getDeclaringClass()->getName() == $classInfoName)
						{
							$hasItsOwnTest = true;
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
	
	
	/**
	 * @param  string
	 * @return void
	 * @todo   optimize
	 */
	protected function importLibrary($path)
	{
		if(substr($path,-2) != '.d' && substr(basename($path),0,1) != '.')
			if($this->_importLibrary($path) && $this->recursive)
				$this->_recursiveImport(new RecursiveDirectoryIterator($path), 1);
	}


	/**
	 * @param  string
	 * @return void
	 * @todo   optimize
	 */
	private function _recursiveImport($it, $depth)
	{
		if($depth > 20)
			die("\nFATAL: max recursion depth reached in ".__FILE__.':'.(__LINE__-1)."\n");
		
		while($it->valid())
		{
			$n = $it->getFilename();
			if($n{0} != '.' && $it->isDir() && substr($n,-2) != '.d')
			{
				#print "__ ".$it->current()->getFilename()." __\n";
				if($this->_importLibrary($it->getPathname()))
					$this->_recursiveImport($it->getChildren(), $depth+1);
			}
			$it->next();
		}
	}
	
	
	/**
	 * @param  string  Abs path
	 * @return boolean Success
	 */
	private function _importLibrary($path)
	{
		if($this->log)
			$this->log->info("Importing library $path ... ");
		$success = import($path);
		if($this->log)
			$this->log->info("OK\n");
		return $success;
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
					$e = new Exception();
					print "\nFATAL unittest error in ".__FILE__.':'.(__LINE__-2)
						. ":\n  Unable to find probable class or interface \"$guessedClass\" for file \n"
						. ABException::formatTrace($e, false);
					exit(1);
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