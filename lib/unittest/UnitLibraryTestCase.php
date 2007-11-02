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
	 * Meant for overriding. This implementation always returns true.
	 * 
	 * @param  ABReflectionClass
	 * @return bool
	 */
	protected function shouldTestClass(ABReflectionClass $classInfo) {
	  return true;
	}
	
	/**
	 * Implementation-specific test logic
	 *
	 * @return void
	 */
	protected function performTests() {
		# Import all class definitions
		if($this->log)
			$this->log->info("Importing libraries...\n");
		$this->importLibrary($this->path);
		if($this->log)
			$this->log->info("Importing classes...\n");
		$this->importClassFiles($this->path);
		
		# Aquire declared classes
		$classes = get_declared_classes();
		$org_cwd = getcwd();
		
		# Find loaded classes and run UnitClassTestCase tests
		foreach($classes as $class) {
			# Has __test method?
			if(is_callable(array($class, '__test'), false)) {
				$classInfo = new ABReflectionClass($class);
				
				if(!$this->shouldTestClass($classInfo)) {
				  continue;
				}
				
				# Disabled this because symlinked libraries might not root in the same
				# directory as we began looking in. The above test should be enough.
				#$declaredInFile = $classInfo->getFileName();
				#if(substr($declaredInFile, 0, strlen($this->path)) == $this->path)
			  
				$hasItsOwnTest = true;
				
				# Only include __test()'s explicitly defined in subclasses
				if($classInfo->getParentClass()) {
					$hasItsOwnTest = false;
					$classInfoName = $classInfo->getName();
					
					foreach($classInfo->getMethods() as $method) {
						if($method->getName() == '__test' && $method->getDeclaringClass()->getName() == $classInfoName) {
							$hasItsOwnTest = true;
							break;
						}
					}
				}
			
				# Create and execute test
				if($hasItsOwnTest) {
				  chdir(dirname($classInfo->getFileName()));
					$this->executeTest(new UnitClassTestCase($class, $classInfo));
				}
			}
		}
		
		chdir($org_cwd);
	}
	
	
	/**
	 * @param  string
	 * @return void
	 * @todo   optimize
	 */
	protected function importLibrary($path) {
		if(substr($path,-2) != '.d' && substr(basename($path),0,1) != '.') {
			if($this->_importLibrary($path) && $this->recursive) {
				$this->_recursiveImport(new RecursiveDirectoryIterator($path), 1);
			}
		}
	}


	/**
	 * @param  string
	 * @return void
	 * @todo   optimize
	 */
	private function _recursiveImport($it, $depth) {
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
	private function _importLibrary($path) {
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
	protected function importClassFiles($path) {
	  $org_cwd = getcwd();
	  chdir($path);
		foreach(scandir($path) as $file) {
			if($file{0} == '.')
				continue;
			
			$filepath = $path.'/'.$file;
			if(strrchr($file, '.') == '.php') {
				if(!preg_match('/^[A-Z]/', $file))
					continue;
				
				$guessedClass = substr($file, 0, -4);
				
				if($this->log) {
					$this->log->debug("Loading class %s from %s/%s ... ", $guessedClass, basename($path), basename($file));
				}
				
				if(!class_exists($guessedClass) && !interface_exists($guessedClass, false)) {
					$e = new Exception();
					print "\nFATAL unittest error in ".__FILE__.':'.(__LINE__-2)
						. ":\n  Unable to find probable class or interface \"$guessedClass\" for file \n"
						. ABException::formatTrace($e, false);
					exit(1);
				}
				
				if($this->log) {
					$this->log->debug("OK\n");
				}
			}
			elseif($this->recursive && is_dir($filepath) && is_readable($filepath)) {
				# Recurse down the alley...
				$this->importClassFiles($filepath);
			}
		}
		chdir($org_cwd);
	}
	
}
?>