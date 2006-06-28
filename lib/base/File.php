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
	 * in order to be deleted or <samp>$recursive</samp> set to true.
	 *
	 * @param  bool
	 * @return void
	 * @throws IOException
	 */
	public function delete($recursive = false) {
		try {
			$url = $this->url->toString();
			if($this->isDir()) {
				if($recursive)
					foreach($this->getFiles() as $file)
						$file->delete(true);
				
				if(!rmdir($url))
					throw new IOException('Failed to delete directory: '.$url);
			}
			else {
				if(!unlink($url))
					throw new IOException('Failed to delete file: '.$url);
			}
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
	 * @return void
	 * @throws IOException
	 */
	public function touch($time = null, $atime = null) {
		try {
			if($time === null)
				touch($this->url->toString());
			elseif($atime === null)
				touch($this->url->toString(), $time);
			else
				touch($this->url->toString(), $time, $atime);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'touch');
		}
	}
	
	/**
	 * Create directory/ies
	 *
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
	 * Recursively create directories
	 *
	 * @param  int
	 * @return void
	 * @throws IOException
	 */
	public function mkdirs( $mode = 0775 ) {
		$this->mkdir($mode, true);
	}
	
	/**
	 * Change mode
	 *
	 * You can pass a octal int as mode (ie. 0755) or you might use a string,
	 * in the common unix chmod format:
	 *
	 *   +x        make writable to world
	 *   a-r       world can not read
	 *   u+w,g+wr  make sure user and group can write and group can read
	 *   =r        everyone can read, no one can write
	 *
	 *
	 * <b>Note:</b> This method will not work on remote files as the file 
	 * to be examined must be accessible via the servers filesystem.
	 *
	 * @param  mixed  <samp>int octal mode</samp> or <samp>string mode</samp>
	 * @return void
	 * @throws IOException
	 * @throws FileNotFoundException
	 */
	public function chmod( $mode )
	{
		try {
			$url = $this->url->toString();
			if(strcasecmp(substr($url,0,7), 'file://') == 0)
				$url = substr($url, 7);
			
			if(is_int($mode))
				chmod($url, $mode);
			else
				$this->chmodstr($url, $mode);
		}
		catch(PHPException $e) {
			if(!$this->exists())
				throw new FileNotFoundException($e, $url);
			$e->rethrow('IOException', 'chmod', 'fileperms');
		}
	}
	
	/**
	 * Change mode, using string
	 *
	 * Examples:
	 *   +x        make executable for owner
	 *   a-r       no one (world) can not read
	 *   u+w,g+wr  make sure user and group can write and group can read
	 *   =r        user can read, nothing else
	 *
	 * @param  string
	 * @return void
	 * @throws PHPException
	 * @throws FileNotFoundException
	 */
	protected function chmodstr($filename, $mode)
	{
		$orgmode = -1;
		$mod = 0;
		$m = array('r'=>0,'w'=>0,'x'=>0);
		$action = '';
		$ogu = '';
		$flush = false;
		$need_flush = false;
		
		# load current mode
		if(strpos($mode,'+') !== false || strpos($mode,'-') !== false) {
			try {
				$mod = fileperms($filename);
			}
			catch(PHPException $e) {
				throw new FileNotFoundException($e, 'Can not modify '.$filename);
			}
		}
		
		$mode_len = strlen($mode);
		for($i=0;$i<$mode_len;$i++)
		{
			$ch = $mode{$i};
			switch($ch)
			{
				case 'u':
				case 'g':
				case 'o':
				case 'a':
					$ogu = $ch;
					break;
				
				case 'r':
					if($ogu == 'u')
						$m['r'] |= 0x100;
					elseif($ogu == 'g')
						$m['r'] |= 0x20;
					elseif($ogu == 'o')
						$m['r'] |= 0x4;
					elseif($ogu == 'a')
						$m['r'] |= 0x124;
					$need_flush = true;
					break;
				
				case 'w':
					if($ogu == 'u')
						$m['w'] |= 0x80;
					elseif($ogu == 'g')
						$m['w'] |= 0x10;
					elseif($ogu == 'o')
						$m['w'] |= 0x2;
					elseif($ogu == 'a')
						$m['w'] |= 0x92;
					$need_flush = true;
					break;
				
				case 'x':
					if($ogu == 'u')
						$m['x'] |= 0x40;
					elseif($ogu == 'g')
						$m['x'] |= 0x8;
					elseif($ogu == 'o')
						$m['x'] |= 0x1;
					elseif($ogu == 'a')
						$m['x'] |= 0x49;
					$need_flush = true;
					break;
				
				case '+':
				case '-':
				case '=':
					$action = $ch;
					if($ogu == '')
						$ogu = 'u';
				
				default:
					$flush = true;
			}
			
			# flush m to mod
			if($flush || $i == $mode_len-1)
			{
				if($need_flush && $action == '+') {
					$mod |= $m['r'];
					$mod |= $m['w'];
					$mod |= $m['x'];
				}
				elseif($need_flush && $action == '-') {
					$mod = $mod & ~$m['r'];
					$mod = $mod & ~$m['w'];
					$mod = $mod & ~$m['x'];
				}
				elseif($need_flush && $action == '=') {
					if($ogu == 'a') {
						$mod = $m['r'] + $m['w'] + $m['x'];
						break; # a= is final
					}
					else {
						# first, clear UGO bit
						if($ogu == 'u')
							$mod = $mod & ~0x1c0;
						elseif($ogu == 'g')
							$mod = $mod & ~0x38;
						elseif($ogu == 'o')
							$mod = $mod & ~0x7;
						# then, set it properly
						$mod |= $m['r'] + $m['w'] + $m['x'];
					}
				}
				
				$flush = false;
				$need_flush = false;
				$m = array('r'=>0,'w'=>0,'x'=>0);
			}
		}
		
		return chmod($filename, $mod);
	}
	
	/**
	 * Change owner
	 *
	 * <b>Note:</b> This method will not work on remote files as the file 
	 * to be examined must be accessible via the servers filesystem.
	 *
	 * @param  mixed  <samp>string Username</samp> or <samp>int UID</samp>
	 * @return void
	 * @throws IOException
	 */
	public function chown( $user )
	{
		try {
			$url = $this->url->toString();
			if(strcasecmp(substr($url,0,7), 'file://'))
				$url = substr($url, 7);
			chown($url, $user);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'chown');
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
	 * Write data to file
	 *
	 * @param  string
	 * @param  bool   Append instead of truncate
	 * @param  bool   Acquire an exclusive lock
	 * @return int    Amount of bytes that were written to the file
	 * @throws IOException
	 */
	public function putContents( $data, $append = false, $lock = false ) {
		try {
			$flags = 0;
			if($append) $flags |= FILE_APPEND;
			if($lock) $flags |= LOCK_EX;
			return file_put_contents($this->url->toString(), $data, $flags);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'file_put_contents');
		}
	}
	
	/**
	 * Read data from file
	 *
	 * @return string  Data
	 * @throws IOException
	 */
	public function getContents() {
		try {
			return file_get_contents($this->url->toString());
		}
		catch(PHPException $e) {
			$e->setMessage($e->getMessage() . ' ' . $this->url->toString());
			$e->rethrow('IOException', 'file_get_contents');
		}
	}
	
	/**
	 * @param  string
	 * @return File[]
	 * @throws IOException
	 */
	public function getFiles()
	{
		$base_dir = rtrim($this->url->toString(),'/').'/';
		$files = array();
		$url = $this->url->toString();
		
		if(!($dh = opendir($url)))
			throw new IOException('Failed to open directory for reading: '.$url);
		
		$basedir = $url . '/';
		while(($filename = readdir($dh)) !== false)
			$files[] = new File($basedir . $filename);
		
		return $files;
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