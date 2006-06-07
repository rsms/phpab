<?
class IMAPMailbox {
	
	protected $name = 'Untitled';
	protected $path = 'INBOX.Untitled';
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
	 * @param  int
	 * @param  string
	 * @param  IMAPConnection
	 */
	public function __construct( $path, $name, $connection = null ) {
		$this->path = $path;
		$this->name = $name;
		$this->connection = $connection;
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
		$this->messages = $nfo->messages;
		$this->recent = $nfo->recent;
		$this->unseen = $nfo->unseen;
	}
	
	
	/**
	 * @param  array
	 * @param  IMAPConnection
	 * @return Mailbox[]
	 * @usedby ImapConnection->getMailboxes()
	 */
	public static function fromList( $list, $connection = null )
	{	
		$boxes = array();
		
		foreach($list as $i => $m)
		{	
			$path = mb_convert_encoding(substr($m->name, strpos($m->name,'}')+1), 'UTF-8', 'UTF7-IMAP');
			
			$p = strrpos($path, $m->delimiter);
			$name = ($p===false) ? $path : substr($path, $p+1);
			
			$mb = new self($path, $name, $connection);
			$mb->delimiter = $m->delimiter;
			$mb->flags = $m->attributes;
			
			$r = explode($m->delimiter, $path);
			
			eval('$boxes[\'' . implode('\'][\'', $r) . '\'][\'#\'] = $mb;');
		}
		
		return $boxes;
	}
	
	
	/** @return string */
	public function getPath( $imapEncoded = false ) {
		if($imapEncoded)
			return mb_convert_encoding($this->path, 'UTF7-IMAP', 'UTF-8');
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
		return '<' . $tagName . ' messages="' . $this->details->Nmsgs 
			. '" unread="' . $this->details->Unread
			. '" recent="' . $this->details->Recent
			. '" size="' . $this->details->Size
			. '" deleted="' . $this->details->Deleted
			. '" name="' . Utils::xmlEscape($this->getName()) 
			. '" path="' . Utils::xmlEscape($this->getPath()) . '"';
	}
}

?>