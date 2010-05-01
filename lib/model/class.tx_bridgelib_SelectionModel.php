<?php
/*
 * Class for ``Selection'' Objects
 */
class tx_bridgelib_SelectionModel extends tx_bridgelib_AbstractDbmodel{
	/**
	 * Construct Object by uid
	 *
	 * @param int uid of the selection
	 */
	public function loadFromUid($uid){
		$table 	= 'tx_bridge_lib_selection';
		$row = t3lib_BEfunc::getRecord($table,$uid,$fields='*',$where='',$useDeleteClause=true);

		if(parent::checkAccess($row['pid'])){
			$this->setName($row['name']);
			$this->setUid($uid);
			$this->setPid($row['pid']);
		}else{
			unset($this);
			die('no access on record');
		}
	}

	/**
	 * Method to set a name of the selection
	 *
	 * @param string name of the selection
	 */
	public function setName($name){
		$this->attributes['name'] = $name;
	}

	/**
	 * Returns the Name of the Selection for a Storagefolder, Spaces will be replaced
	 * with `-'
	 *
	 * @return string
	 */
	public function getNameForStorage(){
		return trim(strtolower(str_replace(' ','-',$this->attributes['name'])));
	}

	/**
	 * Method to get the name of the selection
	 *
	 * @param voidbui
	 * @return string name of the selection
	 */
	public function getName(){
		return $this->attributes['name'];
	}

	/**
	 * Private method to build an array with all relations of this selection.
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function _buildRelations($pid =0 , $use_pid =false){
		unset($this->relations);
		$this->relations = array();

		$select = '*';
		$table 	= 'tx_bridge_lib_selection_contentelement_mm';
		$where	= 'uid_local='.$this->getUid();

		if($use_pid != false){
			$where .= ' AND pid='.$pid;
		}

		$orderby = 'pid, sorting ';
		$rs 	= $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,null,$orderby,null);


		if($GLOBALS['TYPO3_DB']->sql_num_rows($rs) > 0){
			while(($row 	= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs)) != false){
				$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
				$relation->setSelectionUid($row['uid_local']);
				$relation->setContentElementUid($row['uid_foreign']);
				$relation->setPid($row['pid']);
				$relation->setUid($row['uid']);
				$relation->setSorting($row['sorting']);

				$this->relations[] = $relation;
			}
		}

	}

	/**
	 * Method delivers all relation objects
	 *
	 * @return an array with all relations objects of this selection
	 */
	public function getRelations($pid, $use_pid=false){
		$this->_buildRelations($pid,$use_pid);
		return $this->relations;
	}

	/**
	 * Checkes if an ContentElement with the given Uid is in the
	 * Selection or not
	 *
	 * @param int uid
	 * @return boolean
	 */
	public function isContentElementInSelection($celem_id){
		$this->_buildRelations();
		if(is_array($this->relations)){
			foreach($this->relations as $relation){
				if($relation->getContentElementUid() == $celem_id) return true;
			}
		}
		return false;
	}

	/**
	 * Method return the ContentElement of a relation
	 *
	 * @param uid of the relation
	 * @return Object returns the ContentElement
	 */
	public function getContentElementFromSelection($relation_id){
		$this->_buildRelations();
		foreach($this->relations as $relation){
			if($relation->getUid() == $relation_id) return $relation->getContentElement();
		}
		return false;
	}

	/**
	 * This method is used to deliver all Contentelements assigned
	 * to this Selection
	 *
	 * @return array array with all assigned content elements of this selection
	 */
	public function getAllAssignedContentElements(){
		$this->_buildRelations();
		$celements = array();

		if(is_array($this->relations)){
			foreach($this->relations as $relation){
				$celements[] = $relation->getContentElement();
			}
		}
		return $celements;
	}

	/**
	 * This method is used to get all "Selection" Objects from the database
	 *
	 * @param void
	 * @return array of objects
	 */
	public function loadAll(){
		global $BE_USER;

		//array for result objects
		$result_arr = array();

		$select = '*';
		$table = 'tx_bridge_lib_selection';
		$where = 'deleted != 1 '.t3lib_BEfunc::BEenableFields('tx_bridge_lib_selection');

		//get all selection from the database
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,null,null,null);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs)){
			if(parent::checkAccess($row['pid'])){
				$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
				$selection->setName($row['name']);
				$selection->setUid($row['uid']);
				//add the created selection to the resultset
				$result_arr[] = $selection;
			}
		}
		return $result_arr;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_SelectionModel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_SelectionModel.php']);
}
?>