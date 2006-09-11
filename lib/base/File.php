<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class File {
	
	const SORT_ASC  = 1;
	const SORT_DESC = 2;
	
	/** @var string */
	protected $path;
	
	/** @var URL */
	protected $url;
	
	/** @var array */
	private static $deleteOnExit = array();
	
	
	/**
	 * @param  mixed  <samp>string</samp>, <samp>URL</samp> or <samp>File</samp>
	 * @throws IllegalArgumentException if the class of $path is not convertable
	 * @see    valueOf()
	 */
	public function __construct($path)
	{
		if(is_object($path)) {
			if($path instanceof URL) {
				$this->url = $path;
				$this->path = $path->toString();
			}
			elseif($path instanceof File) {
				# copy path and url right of the instance
				$this->path = $path->path;
				$this->url = $path->url;
			}
			else {
				throw new IllegalArgumentException('Can not convert a '.get_class($path).' to a File');
			}
		}
		else
			$this->path = $path;
	}
	
	/** @return URL */
	public function getURL() {
		if(!$this->url)
			$this->url = new URL($this->path);
		return $this->url;
	}
	
	/**
	 * ftp://localhost/foo/bar.txt -> bar.txt
	 * @return string
	 */
	public function getName() {
		return basename($this->getPath());
	}
	
	/**
	 * ftp://localhost/foo/bar -> /foo
	 * @return string
	 */
	public function getDirname() {
		return dirname($this->getPath());
	}
	
	/**
	 * ftp://localhost/foo/../bar -> /foo/../bar
	 * @return File
	 */
	public function getPath() {
		if(!$this->url && strpos($this->path, ':') === false)
			return $this->path;
		return $this->getURL()->getPath();
	}
	
	/**
	 * ftp://localhost/foo/../bar -> /bar
	 * @return File
	 */
	public function getAbsolutePath() {
		if(($p = realpath($this->getPath())) === false)
			return $this->getPath();
		return $p;
	}
	
	/**
	 * ftp://localhost/foo/bar -> ftp://localhost/foo
	 * @return File
	 */
	public function getParent() {
		$class = get_class($this);
		return new $class(dirname(rtrim($this->toString(),'/\\')));
	}
	
	/** @return string */
	public function toString() {
		return $this->url ? $this->url->toString() : $this->path;
	}
	
	/** @return string */
	public function __toString() { return $this->toString(); }
	
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
		$p = strrpos($name, '.');
		$url = $this->getURL();
		
		if(($p = strrpos($name, '.')) !== false)
			$url->setPath($this->getDirname().'/'.substr($name, 0, $p+1).$ext);
		else
			$url->setPath($this->getPath().'.'.$ext);
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isFile() {
		try {
			return is_file($this->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_file');
		}
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isLink() {
		try {
			return is_link(rtrim($this->toString(),'/'));
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_link');
		}
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function isDir() {
		try {
			return is_dir($this->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_dir');
		}
	}
	
	/**
	 * Check if the file or directory is readable
	 *
	 * If you want to make sure a file or directory exists, use the 
	 * much faster {@link exists()} method instead.
	 * 
	 * @return bool
	 * @throws IOException
	 */
	public function isReadable() {
		try {
			return is_readable($this->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'is_readable');
		}
	}
	
	/**
	 * @return bool
	 * @throws IOException
	 */
	public function exists() {
		try {
			return file_exists($this->toString());
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
			return filesize($this->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'filesize');
		}
	}
	
	/**
	 * @return int Timestamp
	 * @throws IOException
	 */
	public function lastModified() {
		try {
			return filemtime($this->toString());
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
			$url = rtrim($this->toString(), '/');
			if(is_file($url) || is_link($url)) {
				if(!unlink($url))
					throw new IOException('Failed to delete '.($this->isFile() ? 'file: ' : 'link: ').$url);
			}
			else {
				if($recursive)
					foreach($this->getFiles() as $file)
						$file->delete(true);
				
				if(!rmdir($url))
					throw new IOException('Failed to delete directory: '.$url);
			}
		}
		catch(PHPException $e) {
			$e->setMessage($e->getMessage() . ': ' . $this->toString());
			$e->rethrow('IOException', 'rmdir', 'unlink');
		}
	}
	
	/**
	 * Requests that the file or directory denoted by this <samp>File</samp> be deleted
	 * when the current PHP process/session terminates.
	 *
	 * <b>Note:</b> Once deletion has been requested, it is not possible to cancel the 
	 *              request. The only option is to change the path/url of the file to 
	 *              point to a non-existent file, for example 
	 *              <samp>$file->getURL()->set("/tmp/notinghere")</samp>
	 * 
	 * @return void
	 */
	public function deleteOnExit() {
		if(!self::$deleteOnExit)
			register_shutdown_function(array('File','__deleteOnExit'));
		self::$deleteOnExit[] = /*clone*/ $this;
	}
	
	/**
	 * @return void
	 * @throws IOException
	 */
	public function touch($time = null, $atime = null) {
		try {
			if($time === null)
				touch($this->toString());
			elseif($atime === null)
				touch($this->toString(), $time);
			else
				touch($this->toString(), $time, $atime);
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
			if(!mkdir($this->toString(), $mode, $recursive))
				throw new IOException('Failed to create directory: '.$this->toString());
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'mkdir');
		}
	}
	
	/**
	 * Recursively create directories
	 *
	 * Delegates to <samp>File->mkdir($mode, true);</samp>
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
			$url = $this->toString();
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
			$url = $this->toString();
			if(strcasecmp(substr($url,0,7), 'file://'))
				$url = substr($url, 7);
			chown($url, $user);
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'chown');
		}
	}
	
	
	/**
	 * Create a link
	 *
	 * @param  mixed  string path or any object whos __toString() results in a valid path
	 * @param  bool   Create a symbiolic (soft) link instead of a hard link
	 * @return void
	 * @throws IOException
	 */
	public function linkTo($linkName, $symbolic = true, $mkdirs = true)
	{
		# sanity check
		if($this->getURL()->getProtocol() && $this->getURL()->getProtocol() != 'file')
			throw new IllegalOperationException('Can not create a link outside the local file system');
		
		# clean up $linkName
		if(!is_string($linkName))
			$linkName = is_object($linkName) ? $linkName->__toString() : strval($linkName);
		$linkName = rtrim($linkName, '/');
		$linkDir = dirname($linkName);
		
		try {
			$thisPath = rtrim($this->getPath(), '/');
			
			if(!file_exists($linkDir) && !is_link($linkDir)) {
				if($mkdirs) {
					mkdir($linkDir, 0775, true);
					@chmod($linkDir, 0775);
				}
				else
					throw new FileNotFoundException('link target parent directory not found "'.$linkDir.'"');
			}
			
			if($symbolic)
				symlink($thisPath, $linkName);
			else
				link($thisPath, $linkName);
		}
		catch(PHPException $e) {
			throw new IOException($e, $e->getMessage());
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
		
		if(!$this->getURL()->isProtocol($what->getURL()->getProtocol()))
			throw new IllegalArgumentException('Protocol mismatch: Old and New destinations must use the same protocol');
		
		try {
			if(!rename($this->toString(), $what->toString()))
				throw new IOException('Failed to rename "'.$this->toString().'" to "'.$what->toString().'"');
		}
		catch(PHPException $e) {
			$e->rethrow('IOException', 'rename');
		}
	}
	
	/**
	 * @param  mixed  string or <samp>File</samp>
	 * @param  bool
	 * @param  int
	 * @param  bool
	 * @param  string Filename match filter of files to skip. Only applies to recursive copy.
	 * @return void
	 * @throws IllegalArgumentException  on protocol mismatch
	 * @throws IOException
	 */
	public function copyTo( $where, $recursive = false, $recDirMode = 0775, $recOverwrite = false, $excludeFilter = null)
	{
		$where = File::valueOf($where);
		$from = $this->toString();
		$to = $where->toString();
		
		try {
			if(!$recursive) {
				copy($from, $to);
			}
			else {
				if($this->isDir()) {
					if(!$where->exists())
						$where->mkdirs($recDirMode);
					$from = rtrim($from,'/');
					$to = rtrim($to,'/');
					$this->_copyDir($from, $to, $recOverwrite, $recDirMode, $excludeFilter);
				}
				else {
					try { $where->getParent()->mkdirs($recDirMode); } catch(Exception $e) {}
					copy($from, $to);
				}
			}
		}
		catch(PHPException $e) {
			throw new IOException($e, 'Copy failed');
		}
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  bool
	 * @return void
	 */
	private function _copyDir( $from, $to, $overwrite, $dirMode, $excludeFilter )
	{
		if($d = opendir($from)) {
			while(($file = readdir($d)) !== false) {
				if($file != '.' && $file != '..')
				{
					if($excludeFilter && fnmatch($excludeFilter, $file))
						continue;
					
					$fromPath = $from . '/' . $file;
					$toPath = $to . '/' . $file;
					
					if(is_dir($fromPath)) {
						mkdir($toPath, $dirMode);
						@chown($toPath, fileowner($fromPath));
						$this->_copyDir($fromPath, $toPath, $overwrite, $dirMode, $excludeFilter);
					}
					elseif(is_file($fromPath) || is_link($fromPath)) {
						if($overwrite || !file_exists($toPath)) {
							copy($fromPath, $toPath);
							@chmod($toPath, fileperms($fromPath));
							@chown($toPath, fileowner($fromPath));
						}
					}
				}
			}
		}
		else {
			throw new IOException('Failed to open directory for listing: '.$from);
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
			return file_put_contents($this->toString(), $data, $flags);
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
			return file_get_contents($this->toString());
		}
		catch(PHPException $e) {
			$e->setMessage($e->getMessage() . ' ' . $this->toString());
			$e->rethrow('IOException', 'file_get_contents');
		}
	}
	
	/**
	 * @param  bool    Include subdirectories and their content
	 * @return File[]
	 * @throws IOException
	 */
	public function getFiles($recursive = false)
	{
		$dir = rtrim($this->toString(), '/') . '/';
		$files = array();
		
		if($recursive) {
			$this->getFilesR($files, $dir);
		}
		else {
			if(!($dh = @opendir($dir)))
				throw new IOException('Failed to open directory for reading: '.$dir);
			
			try {
				while(($file = readdir($dh)) !== false)
					if($file != '.' && $file != '..')
						$files[] = new self($dir.$file);
				closedir($dh);
			}
			catch(Exception $e) {
				closedir($dh);
				throw $e;
			}
		}
		
		return $files;
	}
	
	/**
	 * @param  File[]
	 * @param  string
	 * @param  bool
	 * @return void
	 * @throws IOException
	 */
	private function getFilesR(&$files, $dir)
	{
		$dir = rtrim($dir,'/');
		if(!($dh = @opendir($dir)))
			throw new IOException('Failed to open directory for reading: '.$dir);
		
		try {
			while(($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..') {
					$f = new self($dir.'/'.$file);
					$files[] = $f;
					if($f->isDir())
						$this->getFilesR($files, $f->toString());
				}
			}
			closedir($dh);
		}
		catch(Exception $e) {
			closedir($dh);
			throw $e;
		}
		
		
	}
	
	/**
	 * Get files in directory matching pattern
	 *
	 * Runs {@link ls() File->ls()} and create File isntances
	 *
	 * @param  string  fn-pattern
	 * @param  int     See the File::SORT_-constants. 0 = don't sort
	 * @return File[]
	 * @see    ls()
	 */
	public function listFiles( $pattern = '', $sort = 0)
	{
		$dir = rtrim($this->toString(),'/').'/';
		$files = array();
		foreach($this->ls($pattern, $sort) as $file)
			$files[] = new self($dir.$file);
		return $files;
	}
	
	/**
	 * @param  string  fn-pattern
	 * @param  int     See the File::SORT_-constants. 0 = don't sort
	 * @return string[]
	 * @see    listFiles()
	 */
	public function ls( $pattern = '', $sort = 0 )
	{
		try {
			if ($dh = opendir($this->toString())) {
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
				throw new IOException('Failed to open dir for listing: '.$this->toString());
			}
		} catch(PHPException $e) {
			$e->rethrow('IOException', 'opendir');
		}
	}
	
	/**
	 * @return bool
	 */
	public function isLocal() {
		if(!$this->url && strpos($this->path, ':') === false)
			return true;
		return ($this->getURL()->isProtocol('file') || $this->getURL()->isProtocol(''));
	}
	
	/**
	 * @param  mixed  File or string
	 * @return bool
	 */
	public function equals($file) {
		$file = self::valueOf($file);
		$thisUrl = $this->isLink() ? readlink(rtrim($this->getPath(),'/')) : rtrim($this->toString(),'/');
		$fileUrl = $file->isLink() ? readlink(rtrim($file->getPath(),'/')) : rtrim($file->toString(),'/');
		return $thisUrl == $fileUrl;
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return File
	 */
	public static function createTempFile($prefix = '', $suffix = '', $inDir = '/tmp', $deleteOnExit = true)
	{
		if(($file = tempnam($inDir, $prefix).$suffix) === false)
			throw new IOException('Failed to create temporary file ' . $file);
		
		$file = new File($file);
		@$file->touch();
		@$file->chmod(0664);
		
		if($deleteOnExit)
			$file->deleteOnExit();
		
		return $file;
	}
	
	/**
     * Convert absolute path to a relative path, based in <samp>$relativeToBase</samp>
     *
     * <b>Example</b>
     * <code>
     * print File::relativePath('/absolute/path/to/foo.bar', '/absolute/path');
	 * // output: "to/foo.bar"
     * </code>
     * 
     * @param  string
     * @param  string
     * @return string
     */
    public static function relativePath( $path, $basePath )
    {
		if($basePath) {
			$len = strlen($basePath);
	        if(substr($path, 0, $len) == $basePath)
	            return substr($path, $len + (($basePath{$len-1} != '/') ? 1 : 0));
		}
        return $path;
    }
	
	/**
	 * @param  mixed  string, File or URL
	 * @return File
	 * @throws IllegalArgumentException
	 */
	public static function valueOf($file)
	{
		if($file instanceof File)
			return $file;
		return new self($file);
	}
	
	/** @ignore */
	public static function __deleteOnExit() {
		foreach(self::$deleteOnExit as $file) {
			try { $file->delete(); } catch(Exception $e) {}
		}
	}
	
	/** @ignore */
	public static function __test()
	{
		$file = new File(__FILE__);
		assert($file->exists());
		assert($file->isFile());
		assert(!$file->isDir());
		assert($file->isReadable());
	}
}
?>