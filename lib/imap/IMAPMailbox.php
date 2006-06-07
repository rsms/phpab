<?
class IMAPMailbox {
	
	private $name = 'Untitled';
	private $path = 'INBOX.Untitled';
	private $delimiter = '.';
	private $flags = 0;
	private $details = null;
	
	/**
	 * @param  int
	 * @param  string
	 */
	public function __construct( $path, $name ) {
		$this->path = $path;
		$this->name = $name;
	}
	
	/**
	 * @return int
	 */
	public function getNumMessages() {
		$this->fetchDetailsIfNeeded();
		return $this->details->Nmsgs;
	}
	
	/**
	 * @return int
	 */
	public function getNumUnreadMessages() {
		$this->fetchDetailsIfNeeded();
		return $this->details->Unread;
	}
	
	/**
	 * @return int
	 */
	public function getNumRecentMessages() {
		$this->fetchDetailsIfNeeded();
		return $this->details->Recent;
	}
	
	/**
	 * @return void
	 */
	private function fetchDetailsIfNeeded()
	{
		if(is_object($this->details))
			return;
		
		$conn = new IMAPConnection();
		
		// OP_READONLY gor sa att recent inte andras, men datumet-senast-accessed 
		// andras hur som, sa det ar menlost.
		$conn->open($this->getPath(true), c('imap.user.name'), c('imap.user.password'), OP_READONLY);
		
		$this->details = $conn->fetchMailboxInfo();
	}
	
	
	/**
	 * @param  array
	 * @return Mailbox[]
	 * @usedby ImapConnection->getMailboxes()
	 */
	public static function fromList( $list )
	{	
		$boxes = array();
		
		foreach($list as $i => $m)
		{	
			$path = mb_convert_encoding(substr($m->name, strpos($m->name,'}')+1), 'UTF-8', 'UTF7-IMAP');
			
			$p = strrpos($path, $m->delimiter);
			$name = ($p===false) ? $path : substr($path, $p+1);
			
			$mb = new self($path, $name);
			$mb->delimiter = $m->delimiter;
			$mb->flags = $m->attributes;
			
			$r = explode($m->delimiter, $path);
			
			eval('$boxes[\'' . implode('\'][\'', $r) . '\'][\'#\'] = $mb;');
		}
		
		return $boxes;
	}
	
	
	/** @return string */
	public function getPath( $imapEncoded = false ) {
		if(!$imapEncoded)
			return $this->path;
		return mb_convert_encoding($this->path, 'UTF7-IMAP', 'UTF-8');
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