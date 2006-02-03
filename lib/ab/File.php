<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    hunch.ab
 * @subpackage io
 */
class File {
	
	const SORT_ASC  = 1;
	const SORT_DESC = 2;
	
	/** @var URL */
	protected $url;
	
	private static $deleteOnExit = array();
	
	/**
	 * @param mixed  <samp>URL</samp>, <samp>File</samp> or <samp>string</samp>
	 */
	public function __construct($url) {
		$this->url = URL::valueOf($url);
		$this->url->setQuery(null);
		$this->url->setRef(null);
	}
	
	/** @return string */
	public function getURL() {
		return $this->url;
	}
	
	/**
	 * ftp://localhost/foo/bar -> bar
	 * @return string
	 */
	public function getName() {
		return basename($this->url->getPath());
	}
	
	/**
	 * ftp://localhost/foo/../bar -> /foo/../bar
	 * @return File
	 */
	public function getPath() {
		return $this->url->getPath();
	}
	
	/**
	 * ftp://localhost/foo/../bar -> /bar
	 * @return File
	 */
	public function getAbsolutePath() {
		if(($p = realpath($this->url->getPath())) === false)
			return $this->url->getPath();
		return $p;
	}
	
	/**
	 * ftp://localhost/foo/bar -> /foo
	 * @return string
	 */
	public function getDirname() {
		return dirname($this->url->getPath());
	}
	
	/**
	 * ftp://localhost/foo/bar -> ftp://localhost/foo
	 * @return File
	 */
	public function getParent() {
		$class = get_class($this);
		return new $class(dirname(rtrim($this->url->toString(),'/\\')));
	}
	
	/**
	 * @return string
	 */
	public function toString() {
		#return get_class($this).'<'.$this->url->toString().'>';
		return $this->url->toString();
	}
	
	/**
	 * @return string
	 */
	public function getExtension() {
		$name = $this->getName();
		if(($p = strrpos($name, '.')) !== false)
			return substr($name, $p+1);
		else
			return '';
	}
	
	/**
	 * @param  string
	 * @return void
	 */
	public function setExtension($ext) {
		$name = $this->getName();
		if(($p = strrpos($name, '.')) !== false)
			$this->url->setPath($this->getDirname().'/'.substr($name, 0, $p+1).$ext);
		else
			$this->url->setPath($this->url->getPath().'.'.$ext);
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isFile() {
		try {
			return is_file($this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_file');
		}
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isDir() {
		try {
			return is_dir($this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_dir');
		}
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function exists() {
		try {
			return file_exists($this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'file_exists');
		}
	}
	
	/**
	 * @return int (unsigned)
	 * @throws IOException
	 */
	public function size() {
		try {
			return filesize($this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'filesize');
		}
	}
	
	/**
	 * @return string
	 * @throws IOException
	 */
	public function lastModified() {
		try {
			return filemtime($this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'filemtime');
		}
	}
	
	/**
	 * Deletes the file or directory denoted by this abstract pathname.
	 * If this pathname denotes a directory, then the directory must be empty 
	 * in order to be deleted.
	 *
	 * @param  int
	 * @param  bool
	 * @return void
	 * @throws IOException
	 */
	public function delete() {
		try {
			if(!($this->isDir() ? rmdir($this->url->toString()) : unlink($this->url->toString())))
				throw new IOException('Failed to delete '.$this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'rmdir', 'unlink');
		}
	}
	
	/**
	 * Requests that the file or directory denoted by this <samp>File</samp> be deleted
	 * when the current PHP process/session terminates.
	 *
	 * <b>Note:</b> Once deletion has been requested, it is not possible to cancel the request.
	 * 
	 * @return void
	 */
	public function deleteOnExit() {
		if(!self::$deleteOnExit)
			register_shutdown_function(array('File','__deleteOnExit'));
		self::$deleteOnExit[] = clone $this;
	}
	
	/**
	 * @param  int
	 * @param  bool
	 * @return void
	 * @throws IOException
	 */
	public function mkdir( $mode = 0775, $recursive = false ) {
		try {
			if(!mkdir($this->url->toString(), $mode, $recursive))
				throw new IOException('Failed to create directory: '.$this->url->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'mkdir');
		}
	}
	
	/**
	 * @param  mixed  string or <samp>File</samp>
	 * @return void
	 * @throws IllegalArgumentException  on protocol mismatch
	 * @throws IOException
	 */
	public function renameTo( $what ) {
		$what = File::valueOf($what);
		
		if(!$this->url->isProtocol($what->url->getProtocol()))
			throw new IllegalArgumentException('Protocol mismatch: Old and New destinations must use the same protocol');
		
		try {
			if(!rename($this->url->toString(), $what->url->toString()))
				throw new IOException('Failed to rename "'.$this->url->toString().'" to "'.$what->url->toString().'"');
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'rename');
		}
	}
	
	/**
	 * @param  mixed  string or <samp>File</samp>
	 * @return void
	 * @throws IllegalArgumentException  on protocol mismatch
	 * @throws IOException
	 */
	public function copyTo( $where )
	{
		$where = File::valueOf($where);
		
		try {
			if(!copy($this->url->toString(), $where->url->toString()))
				throw new IOException('Failed to copy "'.$this->url->toString().'" to "'.$where->url->toString().'"');
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'copy');
		}
	}
	
	/**
	 * @param  string  fn-pattern
	 * @param  int     See the File::SORT_-constants. 0 = don't sort
	 * @param  string
	 * @return File[]
	 */
	public function listFiles( $pattern = '', $sort = 0, $class = '' )
	{
		if(!$class)
			$class = get_class($this);
		
		$base_dir = rtrim($this->url->toString(),'/').'/';
		$files = array();
		foreach($this->ls($pattern) as $file)
			$files[] = new $class($base_dir.$file);
		return $files;
	}
	
	/**
	 * @param  string  fn-pattern
	 * @param  int     See the File::SORT_-constants. 0 = don't sort
	 * @return string[]
	 */
	public function ls( $pattern = '', $sort = 0 )
	{
		try {
			if ($dh = opendir($this->url->toString())) {
				$files = array();
				while(($file = readdir($dh)) !== false) {
					if($pattern)
						if(!fnmatch($pattern, $file))
							continue;
					$files[] = $file;
				}
				closedir($dh);
				
				if($sort == 1)
					natcasesort($files);
				elseif($sort == 2)
					rsort($files);
				
				return $files;
			}
			else {
				throw new IOException('Failed to open dir for listing: '.$this->url->toString());
			}
		} catch(PHPException $e) {
			$e->rethrow('IOException', 'opendir');
		}
	}
	
	/**
	 * @return bool
	 */
	public function isLocal() {
		return ($this->url->isProtocol('file') || $this->url->isProtocol(''));
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return File
	 */
	public static function createTempFile($prefix = '', $suffix = '', $inDir = '/tmp') {
		return new File(tempnam($inDir, $prefix).$suffix);
	}
	
	/**
	 * @param  mixed  string, File or URL
	 * @param  File
	 * @throws IllegalArgumentException
	 */
	public static function valueOf($file)
	{
		if($file instanceof File)
			return $file;
		
		if(!($file instanceof URL) && is_string($file))
			$file = URL::valueOf($file);
		
		if($file instanceof URL) {
			if($file->isProtocol('ftp') || $file->isProtocol('ftps')) {
				if(AB::libraryIsLoaded('ftp')) {
					return new FTPFile($file);
				}
			}
			return new File($file);
		}
		
		$t = gettype($file);
		if($t == 'object')
			$t = get_class($file);
		
		throw new IllegalArgumentException('Can not convert '.$t.' to a File object');
	}
	
	/** @ignore */
	public static function __deleteOnExit() {
		foreach(self::$deleteOnExit as $file) {
			try { $file->delete(); } catch(Exception $e) {}
		}
	}
}
?>