<?
class IMAPMailHeader {
	
	// Flags
	const FNew = 1;
	const FRecent = 2;
	const FUnread = 4;
	const FFlagged = 8;
	const FDeleted = 16;
	const FDraft = 32;
	const FAnswered = 64;
	
	private $no = 0;
	private $id = '';
	private $flags = 0;
	private $bytesize = 0;
	
	private $dateReceived = 0;
	private $dateSent = 0;
	
	private $subject = '';
	private $to = null;
	private $from = null;
	private $replyTo = null;
	private $sender = null;
	
	// private constructor
	private function __construct() {}
	
	/** @return bool */
	public function isNew() { return ($this->flags & self::FNew);}
	
	/** @return bool */
	public function isRecent() { return ($this->flags & self::FRecent);}
	
	/** @return bool */
	public function isUnread() { return ($this->flags & self::FUnread);}
	
	/** @return bool */
	public function isFlagged() { return ($this->flags & self::FFlagged);}
	
	/** @return bool */
	public function isDeleted() { return ($this->flags & self::FDeleted);}
	
	/** @return bool */
	public function isDraft() { return ($this->flags & self::FDraft);}
	
	/** @return bool */
	public function isAnswered() { return ($this->flags & self::FAnswered); }
	
	/**
	 * @param  stdObject
	 * @return MailHeader
	 */
	public static function valueOf( stdClass $data )
	{
		$h = new self();
		
		$h->no 			= intval($data->Msgno);
		$h->id 			= trim($data->message_id,'<>');
		$h->bytesize 	= $data->Size;
		
		$h->dateReceived= strtotime($data->Date);
		$h->dateSent 	= $data->udate;
		
		$h->subject 	= Utils::mimeStringDecode($data->Subject);
		
		$h->to 			= $data->to;
		$h->from 		= $data->from;
		$h->replyTo 	= $data->reply_to;
		$h->sender 		= $data->sender;
		
		$h->flags = 0;
		if($data->Recent != '') {
			$h->flags |= self::FRecent;
			if($data->Recent == 'N')
				$h->flags |= self::FNew;
		}
		
		if(($data->Unseen == 'U'))// || ($h->flags & self::FRecent))
			$h->flags |= self::FUnread;
		
		if($data->Answered == 'A')
			$h->flags |= self::FAnswered;
		
		if($data->Deleted == 'D')
			$h->flags |= self::FDeleted;
		
		if($data->Draft == 'X')
			$h->flags |= self::FDraft;
		
		if($data->Flagged == 'F')
			$h->flags |= self::FFlagged;
		
		return $h;
	}
	
	// @return string
	private static function addrToString($addr)
	{
		$str = '';
		$len = count($addr);
		for($i=0;$i<$len;$i++)
		{
			if(!isset($addr[$i]->mailbox) || !isset($addr[$i]->host))
				continue;
			
			if(isset($addr[$i]->personal)) {
				$name = Utils::mimeStringDecode($addr[$i]->personal);
				if($name{0} != '\'')
					$name = "'$name'";
				$str .= $name . ' <';
			}

			$str .= $addr[$i]->mailbox . '@' . $addr[$i]->host;
			
			if(isset($addr[$i]->personal))
				$str .= '>';
			
			if($i < $len-1)
				$str .= ';';
		}
		return $str;
	}
	
	/**
	 * @param  string
	 * @return string
	 */
	public function toXML($nodeName = 'header')
	{
		return '<' . $nodeName
		
			. ' no="' . $this->no . '"'
			. ' id="' . Utils::xmlEscape($this->id) . '"'
			. ' size="' . $this->bytesize . '"'
			. ' datereceived="' . $this->dateReceived . '"'
			. ' datesent="' . $this->dateSent . '"'
			
			. ' subject="' . Utils::xmlEscape($this->subject) . '"'
			
			. ' to="' . Utils::xmlEscape(self::addrToString($this->to)) . '"'
			. ' from="' . Utils::xmlEscape(self::addrToString($this->from)) . '"'
			. ' replyto="' . Utils::xmlEscape(self::addrToString($this->replyTo)) . '"'
			. ' sender="' . Utils::xmlEscape(self::addrToString($this->sender)) . '"'
			
			. ' new="'      . (($this->flags & self::FNew)      ? '1' : '0') . '"'
			. ' recent="'   . (($this->flags & self::FRecent)   ? '1' : '0') . '"'
			. ' unread="'   . (($this->flags & self::FUnread)   ? '1' : '0') . '"'
			. ' flagged="'  . (($this->flags & self::FFlagged)  ? '1' : '0') . '"'
			. ' deleted="'  . (($this->flags & self::FDeleted)  ? '1' : '0') . '"'
			. ' draft="'    . (($this->flags & self::FDraft)    ? '1' : '0') . '"'
			. ' answered="' . (($this->flags & self::FAnswered) ? '1' : '0') . '"'
			
			. ' />';
	}
}
?>
