<?
class IMAPMailboxCollection {
	
	/** @var IMAPMAilbox[] */
	public $boxes = array();
	
	/**
	 * @param array
	 */
	public function __construct($boxes) {
		$this->boxes = $boxes;
	}
	
	
	/** @return string */
	public function toString() {
		return $this->toStringWalker($this->boxes, 0);
	}
	
	
	/** @return string */
	public function toHTML() {
		return $this->toHTMLWalker($this->boxes, 0);
	}
	
	
	/**
	 * @param  bool
	 * @param  int
	 * @return string
	 */
	public function toXML( $prettyPrint = false, $indentLevel = 0 ) {
		return $this->toXMLWalker($this->boxes, $prettyPrint, $indentLevel);
	}
	
	
	/**
	 * @param  mixed
	 * @param  bool
	 * @param  int
	 * @return string
	 */
	protected function toHTMLWalker(&$data, $level)
	{
		$str = '';
		
		# har mbox objekt?
		if(isset($data['#'])) {
			$box = $data['#'];
			
			$str .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level-1);
			$str .= '<a class="mbox'
				. ($box->getNumUnreadMessages() ? ' unread' : '') 
				.'" href="' . urlencode($box->getPath()) . '/">' . $box->getName() 
				. ($box->getNumUnreadMessages() ? ' <b>'.$box->getNumUnreadMessages().'</b>' : '')
				. "</a><br />\n";
			
			unset($data['#']);
		}
		
		# loopa igenom any childs
		foreach($data as $k => $n)
			$str .= $this->toHTMLWalker($n, $level+1);
		
		return $str;
	}
	
	
	/**
	 * @param  mixed
	 * @param  bool
	 * @param  int
	 * @return string
	 */
	protected function toXMLWalker(&$data, $prettyPrint, $level)
	{
		$str = '';
		$hasMbox = isset($data['#']);
		
		# har mbox objekt?
		if($hasMbox) {
			# start node
			if($prettyPrint)
				$str .= str_repeat("\t", $level-1);
			
			$str .= $data['#']->toXMLStartTag('box');
			
			# remove from array
			unset($data['#']);
		}
		
		# loopa igenom any childs
		if(count($data)) {
		
			# stäng node-start
			if($hasMbox)
				$str .= $prettyPrint ? ">\n" : '>';
			
			foreach($data as $k => $n) {
				try {
					$str .= $this->toXMLWalker($n, $prettyPrint, $level+1);
				}
				catch(IMAPException $e) { /* Skip errors */ }
			}
			
			# avsluta node
			if($hasMbox)
				$str .= $prettyPrint ? str_repeat("\t", $level-1) . "</box>\n" : '</box>';
		}
		else {
			# avsluta node
			if($hasMbox)
				$str .= $prettyPrint ? " />\n" : ' />';
		}
		
		return $str;
	}
	
	
	/**
	 * @param  mixed
	 * @param  int
	 * @param  bool
	 * @return string
	 */
	protected function toStringWalker(&$data, $level)
	{
		$str = '';
		
		# har mbox objekt?
		if(isset($data['#'])) {
			$box = $data['#'];
			$str .= str_repeat('   ', $level-1) . $box->getName() . "\n";
			unset($data['#']);
		}
		
		# loopa igenom childs
		foreach($data as $k => $n)
			$str .= $this->toStringWalker($n, $level+1);
		
		return $str;
	}
	
}
?>