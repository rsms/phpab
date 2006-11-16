<?
define('DB_FETCH_ASSOC', 0);
define('DB_FETCH_NUM', 1);
define('DB_FETCH_BOTH', 2);

/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage db
 */
abstract class DBResult {
	
	/**
	 * @param  int
	 * @return mixed
	 */
	abstract public function fetchRow($style = DB_FETCH_ASSOC);
	
	/**
	 * @return array
	 */
	public function fetchAll($style = DB_FETCH_ASSOC) {
		$rows = array();
		while($row = $this->fetchRow($style))
			$rows[] =& $row;
		return $rows;
	}
	
	/**
	 * @return int  Returns -1 if the result has no row information
	 */
	abstract public function rowCount();
	
	/**
	 * @return int  Returns -1 if the result has no column information
	 */
	abstract public function columnCount();
	
}
?>