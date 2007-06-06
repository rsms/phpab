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
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage ftp
 */
class FTPConnection {
	
	protected $conn = null;
	
	/**
	 * @param  mixed  string or URL object
	 * @param  int    Connection timeout in seconds
	 * @throws IOException
	 * @throws IllegalArgumentException
	 */
	public function __construct($url, $timeout = 30) {
		$url = URL::valueOf($url);
		
		try {
			if($url->isProtocol('ftp'))
				$this->conn = ftp_connect($url->getHost('localhost'), $url->getPort(), $timeout);
			elseif($url->isProtocol('ftps'))
				$this->conn = ftp_ssl_connect($url->getHost('localhost'), $url->getPort(), $timeout);
			else	
				throw new IllegalTypeException('Unsupported protocol: '.$url->getProtocol('???'));
		}
		catch(PHPException $e) {
			$e->setMessage(str_replace(array('ftp_connect(): ', 'ftp_ssl_connect(): '), '', $e->getMessage()));
			throw new ConnectException($e);
		}
		
		if($this->conn == false)
			throw new ConnectException('Failed to connect (unknown reason)');
		
		if($url->getUser())
			$this->login($url->getUser(), $url->getPassword(''));
		
		if($url->getPath())
			$this->cd($url->getPath());
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws IllegalStateException   if not connected
	 * @throws AuthenticationException
	 */
	public function login($user, $password = '')
	{
		if(!$this->conn)
			throw new IllegalStateException('Not connected');
		
		try {
			ftp_login($this->conn, $user, $password);
		}
		catch(PHPException $e) {
			$e->setMessage(str_replace('ftp_login(): ', '', $e->getMessage()));
			throw new AuthenticationException($e);
		}
	}
	
	/**
	 * @param  string
	 * @return void
	 * @throws FTPException
	 */
	public function cd( $path ) {
		try {
			ftp_chdir($this->conn, $path);
		}
		catch(PHPException $e) {
			$e->setMessage(str_replace('ftp_chdir(): ', '', $e->getMessage()));
			throw new FTPException($e);
		}
	}
	
	/**
	 * Current directory
	 *
	 * @return string
	 * @throws FTPException
	 */
	public function pwd() {
		try {
			ftp_pwd($this->conn);
		}
		catch(PHPException $e) {
			$e->setMessage(str_replace('ftp_pwd(): ', '', $e->getMessage()));
			throw new FTPException($e);
		}
	}
	
	/**
	 * @param  string
	 * @param  string
	 * @return FTPFile[]
	 * @throws FTPException
	 */
	public function ls( $pattern = '' ) {
		try {
			$pwd = $this->pwd();
			$r = array();
			$files = ftp_nlist($this->conn, '');
			foreach($files as $i => $file) {
				if($pattern && !fnmatch($pattern, $file))
					continue;
				$f = new FTPFile($pwd.'/'.$file);
				$f->conn = $this;
				$r[] = $f;
			}
			return $r;
		}
		catch(PHPException $e) {
			$e->setMessage(str_replace('ftp_nlist(): ', '', $e->getMessage()));
			throw new FTPException($e);
		}
	}
	
	/**
	 * Set timeout for all subsequent network operations.
	 * 
	 * @param  int
	 * @return void
	 * @throws IOException
	 */
	public function setTimeout($seconds) {
		if(!ftp_set_option($this->conn, FTP_TIMEOUT_SEC, $seconds))
			throw new IOException('Failed to set timeout');
	}
	
	/** @ignore */
	public function __destruct() {
		@ftp_close($this->conn);
	}
}
/*
// set up basic connection
$conn_id = @ftp_connect('inhouse.i.spray.se', 21);

// login with username and password
$login_result = @ftp_login($conn_id, 'rasmus', 'tofflorna');

// check connection
if ((!$conn_id) || (!$login_result)) {
	m('ERROR: FTP connection failed!');
	exit(1);
}

$dir = dirname(__FILE__).'/mods/desertcombat/logs';
$remote_dir = '/www/dev/rasmus/bflogparser/gamelogs/queue';

$files = array_merge(glob($dir.'/*.xml'), glob($dir.'/*.zxml'));

if(count($files)) {
	m("DIR.LOCAL:  \"$dir\"");
	m("DIR.REMOTE: \"$remote_dir\"");
}

foreach($files as $file) {
	$remote_file = $remote_dir.'/'.basename($file);
	m('PUT: "'.basename($file).'"');
	if(!ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
		m("ERROR: Failed to upload $file to ftp://inhouse{$remote_file}");
	}
	else {
		unlink($file);
	}
}

// close the FTP stream
ftp_close($conn_id);*/
?>