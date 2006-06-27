<?
class IMAPMailbox {
	
	protected $name = 'Untitled';
	protected $path = 'INBOX.Untitled';
	protected $encoded_path = 'INBOX.Untitled';
	protected $delimiter = '.';
	protected $flags = 0;
	
	/** @var IMAPConnection  The connection which created this mailbox, or null if none */
	protected $connection = null;
	
	// These are loaded by calling IMAPConnection->getMailboxStatus($this->getPath(true))
	/** @var int  Total number of messages */
	protected $messages = -1;
	
	/** @var int  Number of unread messages */
	protected $recent = -1;
	
	/** @var int  Number of new/unseen, unread messages */
	protected $unseen = -1;
	
	
	/**
	 * @param  string
	 * @param  string
	 * @param  IMAPConnection
	 */
	public function __construct( $encoded_path, $connection = null ) {
		$this->path = mb_convert_encoding($encoded_path, IMAPConnection::getEncoding(), 'UTF7-IMAP');
		$this->encoded_path = $encoded_path;
		$this->connection = $connection;
		
		$p = strrpos($this->path, $this->delimiter);
		$this->name = ($p===false) ? $this->path : substr($this->path, $p+1);
	}
	
	/**
	 * @return int
	 */
	public function getNumMessages() {
		if($this->messages == -1)
			$this->fetchDetails();
		return $this->messages;
	}
	
	/**
	 * @return int
	 */
	public function getNumUnreadMessages() {
		if($this->unseen == -1)
			$this->fetchDetails();
		return $this->unseen;
	}
	
	/**
	 * @return int
	 */
	public function getNumRecentMessages() {
		if($this->recent == -1)
			$this->fetchDetails();
		return $this->recent;
	}
	
	/**
	 * @return void
	 */
	public function fetchDetailsIfNeeded()
	{
		if($this->messages == -1)
			$this->fetchDetails();
	}
	
	/**
	 * @return void
	 */
	protected function fetchDetails()
	{
		# Create new connection if it's missing
		if(!$this->connection) {
			$this->connection = new IMAPConnection();
			$this->connection->open($this->getPath(true), null, null, OP_HALFOPEN);
		}
		
		$nfo = $this->connection->getMailboxStatus($this->getPath(true), SA_MESSAGES|SA_RECENT|SA_UNSEEN);
		if(!is_object($nfo))
			var_dump($nfo);
		$this->messages = $nfo->messages;
		$this->recent = $nfo->recent;
		$this->unseen = $nfo->unseen;
	}
	
	
	/**
	 * @param  array
	 * @param  IMAPConnection
	 * @return Mailbox[]  Multi dimensional map, keyed by imap-encoded path's
	 * @usedby ImapConnection->getMailboxes()
	 */
	public static function fromList( $list, $connection = null )
	{	
		$boxes_temp = array();
		
		foreach($list as $i => $m)
		{	
			$mb = new self(substr($m->name, strpos($m->name,'}')+1), $connection);
			$mb->delimiter = $m->delimiter;
			$mb->flags = $m->attributes;
			$boxes_temp[$mb->getPath()] = $mb;
		}
		
		# Sort
		$boxes = array();
		ksort($boxes_temp);
		foreach($boxes_temp as $mb)
		{
			$r = explode($m->delimiter, $mb->getPath(true));
			eval('$boxes[\'' . implode('\'][\'', $r) . '\'][\'#\'] = $mb;');
		}
		
		return $boxes;
	}
	
	
	/** @return string */
	public function getPath( $imapEncoded = false ) {
		if($imapEncoded)
			return $this->encoded_path;
		else
			return $this->path;
	}
	
	
	/** @return string */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Check if this mailbox has "children" (there are mailboxes below this one)
	 * 
	 * @return bool
	 */
	public function hasChilds() {
		return !(($this->flags & LATT_NOINFERIORS) == LATT_NOINFERIORS);
	}
	
	/**
	 * If this is a container (not a mailbox) you cannot open it.
	 * 
	 * @return bool
	 */
	public function canOpen() {
		return !(($this->flags & LATT_NOSELECT) == LATT_NOSELECT);
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function toXMLStartTag( $tagName = 'box' )
	{	
		$this->fetchDetailsIfNeeded();
		return '<' . $tagName . ' messages="' . $this->messages 
			. '" unread="' . $this->unseen
			. '" recent="' . $this->recent
			. '" name="' . Utils::xmlEscape($this->getName()) 
			. '" path="' . Utils::xmlEscape($this->getPath(true)) . '"';
	}
}

?>