<?
class IMAPConnection {
	
	public $mbox = false;
	
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
	public function open( $path, $user, $password, $options = 0, $extraFlags = '' )
	{	
		$url = '{' 
			. c('imap.host','127.0.0.1') . ':' 
			. c('imap.port',143)
			. c('imap.flags','') . $extraFlags
			. '}' . $path;
		
		if(!($this->mbox = @imap_open($url, $user, $password, $options)))
			throw new ConnectException(imap_last_error());
		else {
			$e = imap_errors();
			if(is_array($e))
				throw new ConnectException(end($e) . ' url: ' . $user . ':' . str_repeat('*',strlen($password)) . '@' . $url);
		}
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
		
		return IMAPMailbox::fromList($list);
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
	
	/**
	 * @return void
	 */
	public function __destruct() {
		$this->close();
	}
}
?>