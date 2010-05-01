<?php
/**
 * An instance of this class represents an typo3 content element in context of selections.
 * An instance can be loaded from the values stored in the database by using the method
 * loadFromUid();
 */
class tx_bridgelib_ContentelementModel extends tx_bridgelib_AbstractDbmodel{
	private $icon;

	/**
	 * Method to set the header of a contentelement
	 *
	 * @param string
	 */
	public function setHeader($header){
		$this->attributes['header'] = $header;
	}

	/**
	 * Method to get the header of the contentelement
	 *
	 * @return void
	 */
	public function getHeader(){
		return $this->attributes['header'];
	}

	/**
	 * Setter Method to set the flah whether this records is a translation or not
	 *
	 * @param boolean
	 */
	public function setTranslation($boolean){
		$this->attributes['is_translation'] = $boolean;
	}

	/**
	 * Returns true if this Record is a translation
	 */
	public function isTranslation(){
		return $this->attributes['is_translation'];
	}

	/**
	 * Method to get the uid of the translation parent record
	 *
	 * @return int
	 */
	public function getL18NParent(){
		return $this->attributes['l18n_parent'];
	}

	public function setL18NParent($uid){
		$this->attributes['l18n_parent'] = $uid;
	}

	public function getIcon(){
		return $this->icon;
	}

	public function setIcon($icon){
		$this->icon = $icon;
	}

	/**
	 * Method to initialize a Contentelement Model from the primaykey
	 *
	 * @param int
	 * @return void
	 */
	public function loadFromUid($uid){
		$select = '*';
		$table 	= 'tt_content';
		$row = t3lib_BEfunc::getRecordWSOL($table,$uid,$fields='*',$where='',$useDeleteClause=true);

		if(parent::checkAccess($row['pid'])){
			$this->setUid($row['uid']);
			$this->setHeader($row['header']);
			$this->setPid($row['pid']);
			
			if($row['l18n_parent'] != 0){
				$this->setTranslation(true);
				$this->setL18NParent($row['l18n_parent']);
			}
		}

		$this->setIcon(t3lib_iconworks::getIcon($table,$row));
	}

	/**
	 * Method to get an Array with all contentelment records as ContentModel instance
	 *
	 * @param int id of the sysfolder where the records are stored
	 * @return array array with content element model objects
	 */
	public function loadAll($pid){

		$celem_array = array();
		$table = 'tt_content';
		$records = t3lib_BEfunc::getRecordsByField($table,'pid',$pid);

		if(is_array($records)){
			foreach($records as $row){
				if(parent::checkAccess($pid)){

					//if we are in a workspace mode, field should be overlayed with workspace data
					t3lib_BEfunc::workspaceOL($table,$row);

					$elem = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
					$elem->setUid($row['uid']);
					$elem->setHeader($row['header']);
					$elem->setPid($row['pid']);

					//if theres an l18nparent this record is a translation
					if($row['l18n_parent'] != 0){
						$elem->setTranslation(true);
						$elem->setL18NParent($row['l18n_parent']);
					}
					$elem->setIcon(t3lib_iconworks::getIcon($table,$row));

					$celem_array[] = $elem;
				}
			}
		}

		return $celem_array;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_ContentelementModel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_ContentelementModel.php']);
}
?>