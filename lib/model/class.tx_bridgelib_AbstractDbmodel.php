<?php
/**
 * Abstract base class for all models in the bridge_lib extension.
 *
 */
abstract class tx_bridgelib_AbstractDbmodel{
	protected $attributes;
	protected $relations;

	/**
	 * set the uid of the sysfolder where the celemt is stored. The pid is the uid of
	 * the record in the pages table, which represents the pagetree
	 *
	 * @param int uid of the sysfolder where the record should be stored
	 */
	public function setPid($pid){
		$this->attributes['pid'] = $pid;
	}

	/**
	 * Method to get the pid (sysfolder uid) of the record
	 */
	public function getPid(){
		return $this->attributes['pid'];
	}

	/**
	 * Setter method for the primary key (uid)
	 *
	 * @param int uid
	 */
	public function setUid($uid){
		$this->attributes['uid'] = $uid;
	}

	/**
	 * Returns the primarykey of the contentelement
	 *
	 * @param void
	 * @return int
	 */
	public function getUid(){
		return $this->attributes['uid'];
	}

	/**
	 * Method to check if the user has access on to the sysfolder where the record is stored.
	 * This method should be used each time a record is created.
	 *
	 * @param int pid pid value of the record
	 *
	 */
	protected function checkAccess($pid){
		global $BE_USER;
		if($BE_USER->isInWebMount($pid,null,0)){
			return true;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_AbstractDbmodel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_AbstractDbmodel.php']);
}
?>