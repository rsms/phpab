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
 * @subpackage imap
 */
class IMAPConnection {
	
	/** @var resource */
	public $mbox = false;
	
	/** @var string */
	public static $OUTPUT_ENCODING = 'ISO-8859-1';
	
	/**
	 * Open connection
	 * 
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  int
	 * @param  string
	 * @return void
	 * @throws ConnectException
	 */
	public function open( $path = '', $user = null, $password = null, $options = 0, $extraFlags = '' )
	{	
		$url = $this->getIMAPURL($path, $extraFlags);
		
		if($user === null)
			$user = c('imap.user.name');
		
		if($password === null)
			$password = c('imap.user.password');
		
		if(!($this->mbox = @imap_open($url, $user, $password, $options)))
			throw new ConnectException(imap_last_error());
		else {
			$e = imap_errors();
			if(is_array($e))
				throw new ConnectException(end($e) . ' url: ' . $url . ':' . str_repeat('*',strlen($password)) . '@' . $url);
		}
	}
	
	/**
	 * Return a full IMAP URL for a path. ie "{myhost:443/notls}INBOX.Stuff"
	 *
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function getIMAPURL($path = '', $extraFlags = '') {
		return '{' 
			. c('imap.host','127.0.0.1') . ':' 
			. c('imap.port',143)
			. c('imap.flags','') . $extraFlags
			. '}' . $path;
	}
	
	
	/**
	 * @return void
	 */
	public function close() {
		if($this->isConnected())
			imap_close($this->mbox);
	}
	
	
	/**
	 * Date	    date of last change
	 * Driver	driver
	 * Mailbox	name of the mailbox
	 * Nmsgs	number of messages
	 * Recent	number of recent messages
	 * Unread	number of unread messages
	 * Deleted	number of deleted messages
	 * Size	    mailbox size
	 * 
	 * @return stdObject
	 */
	public function fetchMailboxInfo() {
		$this->checkConnected();
		return imap_mailboxmsginfo($this->mbox);
	}
	
	/**
	 * Fetch status information on a mailbox other than the current one.
	 *
	 * <b>Possible values for $fetchAll:</b>
	 *   - SA_MESSAGES - set status->messages to the number of messages in the mailbox
	 *   - SA_RECENT - set status->recent to the number of recent messages in the mailbox
	 *   - SA_UNSEEN - set status->unseen to the number of unseen (new) messages in the mailbox
	 *   - SA_UIDNEXT - set status->uidnext to the next uid to be used in the mailbox
	 *   - SA_UIDVALIDITY - set status->uidvalidity to a constant that changes when uids for the mailbox may no longer be valid
	 *   - SA_ALL - set all of the above
	 * 
	 * <b>Return value/object:</b>
	 * <code>
	 * class stdObject {
	 *   public $messages = 0;
	 *   public $recent = 0;
	 *   public $unseen = 0;
	 *   public $uidnext = "";
	 *   public $uidvalidity = 0;
	 * }
	 * </code>
	 *
	 * @param  string     Mailbox path. ie "INBOX.Stuff"
	 * @param  int        What info to be fetched
	 * @return stdObject  Object with properties: int messages, int recent, int unseen, string uidnext, int uidvalidity
	 * @throws IMAPException
	 */
	public function getMailboxStatus($path, $what = SA_ALL) {
		$this->checkConnected();
		if(!($stat = imap_status($this->mbox, $this->getIMAPURL($path), $what)))
			throw new IMAPException('imap_status(conn, "'.$this->getIMAPURL($path).'", '.$what.') failed. ' . imap_last_error());
		return $stat;
	}
	
	
	/**
	 * @return IMAPMailHeader[]
	 * @throws IMAPException
	 * @throws IllegalStateException
	 */
	public function getHeaders()
	{	
		$this->checkConnected();
		$headers = imap_headers($this->mbox);
		self::checkErrors();
		$numHeaders = count($headers);
		for($i=0;$i<$numHeaders;$i++)
			$headers[$i] = IMAPMailHeader::valueOf(imap_header($this->mbox, $i+1));
		return $headers;
	}
	
	
	/**
	 * Get a collection of mailboxes
	 *
	 * There are two special characters you can pass as part of the pattern: '*' and '%'.
	 * '*' means to return all mailboxes. If you pass pattern as '*', you will get a list of the 
	 * entire mailbox hierarchy. '%' means to return the current level only. '%' as the pattern 
	 * parameter will return only the top level mailboxes; '~/mail/%' on UW_IMAPD will return 
	 * every mailbox in the ~/mail directory, but none in subfolders of that directory.
	 *
	 * @param  string
	 * @return IMAPMailbox[]
	 * @throws IMAPException
	 * @throws IllegalStateException
	 */
	public function getMailboxes( $glob = '*' )
	{	
		$this->checkConnected();
		
		if(!$glob)
			$glob = '*';
		
		$list = imap_getmailboxes($this->mbox, '{' . c('imap.host','localhost') . '}', $glob);
		if(!is_array($list))
			throw new IMAPException('Failed to list mailboxes');
		
		return IMAPMailbox::fromList($list, $this);
	}
	
	/**
	 * @return bool
	 */
	public function isConnected() {
		return $this->mbox ? true : false;
	}
	
	/** @return void */
	private function checkConnected() {
		if(!$this->isConnected())
			throw new IllegalStateException('Not connected');
	}
	
	/** @return void */
	private static function checkErrors() {
		$e = imap_errors();
		if(is_array($e))
			throw new ConnectException(end($e));
	}
	
	/** @return string Ouptut encoding (ie. ISO-8859-1 or UTF-8) */
	public static function getEncoding() {
		return c('imap.encoding');
	}
	
	/**
	 * @return void
	 */
	public function __destruct() {
		$this->close();
	}
}
?>