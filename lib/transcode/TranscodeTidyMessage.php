<?
/**
 * @package    ab
 * @subpackage transcode
 * @version    $Id$
 * @author     Rasmus Andersson
 */
class TranscodeTidyMessage extends TranscodeMessage {
	public $code = '';
	public $line = 0;
	public $column = 0;
	
	public function __construct($message, $line, $column, $code) {
		$this->message = $message;
		$this->line = $line;
		$this->column = $column;
		$this->code = $code;
	}
}
?>