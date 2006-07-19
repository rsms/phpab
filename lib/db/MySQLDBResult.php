<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage db
 */
class MySQLDBResult extends DBResult {
	
	/** @var resource mysql result */
	protected $res = null;
	
	/**
	 * @param resource mysql result
	 */
	public function __construct($res) {
		$this->res = $res;
	}
	
	/**
	 * @param  int
	 * @return mixed
	 */
	abstract public function fetchRow($style = DB_FETCH_ASSOC)
	{
		if($style == DB_FETCH_ASSOC)
			return mysql_fetch_assoc($this->res);
		elseif($style == DB_FETCH_NUM)
			return mysql_fetch_row($this->res);
		/*elseif($style == DB_FETCH_OBJ)
			return mysql_fetch_object($this->res);*/
		else
			return mysql_fetch_array($this->res, MYSQL_BOTH);
	}
	
	/**
	 * @return int  Returns -1 if the result has no row information
	 */
	public function rowCount() {
		if(($cols = mysql_num_rows($this->res)) === false)
			return -1;
		return $cols;
	}
	
	/**
	 * @return int  Returns -1 if the result has no column information
	 */
	public function columnCount() {
		if(($cols = mysql_num_fields($this->res)) === false)
			return -1;
		return $cols;
	}
	
}

?>