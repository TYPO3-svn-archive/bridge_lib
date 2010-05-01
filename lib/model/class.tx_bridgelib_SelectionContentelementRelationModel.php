<?php
/**
 * An Instrance of this class represents the relation between an contentelement and a printselection.
 * These relations are represented as a tree. Each relation can have a parent an childs. Relations on the mainlevel
 * have the pid 0 as parent id.
 */
class tx_bridgelib_SelectionContentelementRelationModel extends tx_bridgelib_AbstractDbmodel{
	private $originrow;
	private $hasSorting;
	private $parent;
	private $children;
	private $table = "tx_bridge_lib_selection_contentelement_mm";

	private $debug = true;

	/**
	 * This Method is used to initialize a relationobject of contentelements and selection
	 * by using the primarykey of the contentelement and selection. Both key are foreignkeys
	 * in the relationtable.
	 *
	 * @param int
	 * @param int
	 */
	public function loadFromLocalAndForeignUid($uid_local,$uid_foreign){
		$this->loadFrom("uid_foreign=".$uid_foreign." AND uid_local=".$uid_local);
	}

	/**
	 * This Method is used to initialize a relationobject of contentelements and selections.
	 *
	 * @param int uid the primarykey of the relation
	 */
	public function loadFromUid($uid){
		$this->loadFrom("uid=".$uid);
	}

	/**
	 * this private Method is used to initiliaze the Object from the databasetable. The argumtent of
	 * the method is the where clause, which will be used to get the Object from the database.
	 *
	 * @param string
	 */
	private function loadFrom($where){
		$select = "*";
		$limit 	= "1";

		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table,$where,null,null,$limit);

		if($GLOBALS['TYPO3_DB']->sql_num_rows($rs) > 0){
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);

			$this->originrow = $row;
			$this->setUid($row['uid']);
			$this->setPid($row['pid']);
			$this->setSelectionUid($row['uid_local']);
			$this->setContentElementUid($row['uid_foreign']);
			$this->setSorting($row['sorting']);
		}
	}

	/**
	 * Method is used to set the uid of the selection in the relation.
	 *
	 * @param int
	 */
	public function setSelectionUid($val){
		$this->attributes['uid_local'] = $val;
	}

	/**
	 * Retrieves the uid of the selection in the relation.
	 *
	 * @return int
	 */
	public function getSelectionUid(){
		return $this->attributes['uid_local'];
	}

	/**
	 * Method is used ti set the uid of the contentelement in the relation
	 *
	 * @param int
	 */
	public function setContentElementUid($val){
		$this->attributes['uid_foreign'] = $val;
	}

	/**
	 * Retrieves the uid of the content element in the relation.
	 *
	 * @return int
	 */
	public function getContentElementUid(){
		return $this->attributes['uid_foreign'];
	}


	/**
	 * Method to set the parentid of the relation.
	 *
	 * @param int
	 */
	public function setPid($pid){
		$this->attributes['pid'] = $pid;
		//reset the parent object
		unset($this->parent);
	}

	/**
	 * Method is used to set a sorting value for this relation.
	 * The sorting reprents the order of elements on the same level (with the same parentid)
	 *
	 * @param int
	 */
	public function setSorting($sorting){
		$this->hasSorting = true;
		$this->attributes['sorting'] = $sorting;
	}

	/**
	 * If a sorting value has been set, this method returns true
	 *
	 * @return boolean
	 */
	public function hasSorting(){
		return $this->hasSorting;
	}

	/**
	 * Return the sorting value of this relation
	 *
	 * @return int
	 */
	public function getSorting(){
		return $this->attributes['sorting'];
	}

	/**
	 * Method internally increments the sorting of the Relation. The sorting ist
	 * the order of elements on the treelevel
	 */
	public function incrementSorting(){
		$this->attributes['sorting']++;
	}

	/**
	 * If the sorting has changed or the parent has changed the node has been moved
	 *
	 * @return boolean
	 */
	function hasMoved(){
		return (($this->attributes['sorting'] != $this->originrow['sorting'])
		|| ($this->attributes['pid'] != $this->originrow['pid']));
	}

	/**
	 * Return the treelevel of the relation
	 *
	 * @return int
	 */
	public function getLevel(){
		$last_pid = $this->getPid();
		$count = 0;
		while($last_pid != 0){
			$select = "pid";
			$where  = "uid=".$last_pid;
			$limit  = "1";
			$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table,$where);
			$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
			$last_pid = $res['pid'];
			$count++;
		}
		//to travers to root and count
		return $count;
	}

	private function countEntrys(){	
		$select = "count(*) as anz";
		$where  = "uid_foreign=".$this->getContentElementUid()." AND uid_local=".$this->getSelectionUid();
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table,$where);
		$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
		return $res['anz'];
	}	
	
	/**
	 * Method to save the relation in the database
	 */
	public function save(){
		//check access
		global $BE_USER;
		$selection = $this->getSelection();
		$celement = $this->getContentElement();
		if(!($BE_USER->isInWebMount($selection->getPid(),null,1) && $BE_USER->isInWebMount($celement->getPid(),null,1))) die('access denied');

		$anz = $this->countEntrys();
		
		if(!$this->hasSorting()){ 
			$this->setSorting($this->getHighestSortingOnLevel()+1);
		}elseif($this->hasMoved() && $anz > 0){
			/**
			 * On this point we know that the record has been moved. We have to do diffrent things now.
			 *
			 * if the record has been move from one treelevel to another, there will be gap in the sorting of the records
			 * in this case, all record behind the old place need to be shifted forward. All records on the new place need
			 * to bee shifted back, to get a gap for the record.
			 *
			 */

			if ($this->debug) t3lib_div::devLog ('sorting changend','tx_bridge_lib');

			if($this->isOnSameLevel()){
				if($this->isForwardMove()){
					//if the element has moved forward
					if ($this->debug) t3lib_div::devLog ('forward move','tx_bridge_lib');
					$this->updateSortingOnRecord('decrement',"pid=".$this->getPid().
												" AND sorting <= ".$this->getSorting()." AND sorting > ".$this->originrow['sorting'].
												" AND uid_local=".$this->getSelectionUid().
												" AND uid!=".$this->getUid());
				}elseif($this->isBackwardMove())	{

					if ($this->debug) t3lib_div::devLog ('backward move','tx_bridge_lib');
					$this->updateSortingOnRecord('increment',"pid=".$this->getPid().
												" AND sorting >= ".$this->getSorting()." AND sorting < ".$this->originrow['sorting'].
												" AND uid_local=".$this->getSelectionUid().
												" AND uid!=".$this->getUid());
				}
			}else{
				if ($this->debug) t3lib_div::devLog ('level move','moc');

				//element was dragged from the same tree from a diffrent level
				//-> all element in the old branch behind need to be shifted back, all element in the new brach behind needs to be shifted

				$this->updateSortingOnRecord('decrement',"pid=".$this->originrow['pid'].
														 " AND sorting > ".$this->originrow['sorting'].
														 " AND uid_local=".$this->getSelectionUid().
 														 " AND uid!=".$this->getUid());

				$this->updateSortingOnRecord('increment',"pid=".$this->getPid()." AND sorting >= ".$this->getSorting()." AND uid_local=".$this->getSelectionUid());
			}

		}elseif($anz == 0){

			if ($this->debug) t3lib_div::devLog ('insert new element shifting bebind ','tx_bridge_lib');
			$this->updateSortingOnRecord('increment', "pid=".$this->getPid()." AND sorting >= ".$this->getSorting()." AND uid_local=".$this->getSelectionUid());

		}else{
			if ($this->debug) t3lib_div::devLog ('not moved','moc');
		}

		if($anz  >= 1){
			//update
			$where  = "uid_foreign=".$this->getContentElementUid()." AND uid_local=".$this->getSelectionUid();
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,$where,$this->attributes);
		}else{
			//insert
			$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table,$this->attributes);
			$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
			$this->setUid($uid);
		}
	}

	private function updateSortingOnRecord($sorting_direction,$where){
		if($sorting_direction == 'increment'){$sorting_val = 'sorting+1';}else{$sorting_val = 'sorting-1';}
		$query = "UPDATE ".$this->table." set sorting=".$sorting_val." WHERE ".$where;
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	/**
	 * Method return the Parentobject of this relation.
	 */
	public function getParent(){
		if(!isset($this->parent)){
			$this->parent = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
			$this->parent->loadFromUid($this->getPid());
		}

		return $this->parent;
	}
	
	/**
	* Method to get all Childrecords of an relation
	* 
	* @return array array with relation child records
	*/
	public function getChildren(){
		$this->children = array();
	
		$where = "pid = ".$this->getUid();
		$select = "uid";		
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table,$where);
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs)){
			$child = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
			$child->loadFromUid($row['uid']);
			$this->children[] = $child;
		}
		
		return $this->children;
	}
	
	/**
	 * Method to determine the record is on the same level, after the record has been moved
	 *
	 * @return boolean
	 */
	private function isOnSameLevel(){
		return ($this->getPid() == $this->originrow['pid']);
	}

	/**
	 * If an element was moved this method can be used to determine if the
	 * relation has been moved forward(the new sorting value is higher than the old) or not
	 *
	 * @return boolean
	 */
	private function isForwardMove(){
		return ($this->getSorting() > $this->originrow['sorting']);
	}

	/**
	 * If an element was moved this method can be used to determine if the
	 * relation has been moved backward (the new sorting value is less than the old)
	 *
	 * @return boolean
	 */
	private function isBackwardMove(){
		return ($this->getSorting() < $this->originrow['sorting']);
	}


	/**
	 * Return the Contentelement Object of this relation.
	 *
	 * @return Object contentelement
	 */
	public function getContentElement(){
		$content_element = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$content_element->loadFromUid($this->getContentElementUid());

		return $content_element;
	}

	/**
	 * Returns the Selection Object of this relation
	 *
	 * @return Object selection
	 */
	public function getSelection(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid($this->getSelectionUid());

		return $selection;
	}

	/**
	 * Checks if the contentlement of this relation is exported or not
	 *
	 * @return boolean
	 */
	public function isExported(){
		//determine selection name
		$selection = $this->getSelection();
		$filename = $selection->getNameForStorage();

		$rel_path = $filename."/xml/".$this->getContentElementUid().".xml";

		$file_storage_dir = 'fileadmin/xmlexport/';
		$path = $file_storage_dir.$rel_path;

		$abs_path = t3lib_div::getFileAbsFileName($path);
		return is_file($abs_path);
	}

	/**
	 * Deletes the relation from the database
	 */
	public function deleteRelation(){
		$where  = "(uid_foreign=".$this->getContentElementUid().
 				  " AND uid_local=".$this->getSelectionUid().
 				  ") OR (uid=".$this->getUid().")";

		$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->table,$where);
	}

	/**
	* Deletes the relation recursive with all childrecords from the database.
	* 
	*/
	public function delete(){
		$this->traversAndDeleteChildren($this);
		$this->deleteRelation();
	}
	
	/**
	* Recursive method to delete all Chid
	*/
	private function traversAndDeleteChildren($relation){
		$children = $relation->getChildren();
		
		if(is_array($children)){
			foreach($children as $child){
				$this->traversAndDeleteChildren($child);
				$child->delete();
			}
		}else{
			return true;
		}
	}	

	
	/**
	 * Return the highest sorting value on the level of this relation.
	 *
	 * @return int highest sorting value
	 */
	public function getHighestSortingOnLevel(){
		$select = "max(sorting) as maxsort";
		$where  = 	" uid_local=".$this->getSelectionUid().
  					" AND pid=".$this->getPid();

		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table, $where);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
		return $row['maxsort'];
	}

	/**
	 * Return the previos relation of this relation
	 *
	 * @return object
	 */
	public function getPrevious(){
		$select = "uid_local, uid_foreign";
		$where	= 	"sorting < ".$this->getSorting().
 					" AND uid_local=".$this->getSelectionUid().
 					" AND pid=".$this->getPid();

		$orderby = "sorting DESC";
		$limit = "1";
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table, $where, null, $orderby,$limit);

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
		$res = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$res->loadFromLocalAndForeignUid($row['uid_local'],$row['uid_foreign']);
		return $res;
	}

	/**
	 * Checks if theres a relation on the same level, behind this one(sorting is higher)
	 *
	 * @return boolean
	 */
	public function hasNext(){
		if($this->getSorting() < $this->getHighestSortingOnLevel()){
			return false;
		}else{
			return true;
		}
	}

	/**
	 * Returns the next relation object on the same level
	 *
	 * @return object
	 *
	 */
	public function getNext(){
		$select = "uid_local, uid_foreign";
		$where	= 	"sorting > ".$this->getSorting().
 					" AND uid_local=".$this->getSelectionUid().
 					" AND pid=".$this->getPid();

		$orderby = "sorting ASC";
		$limit = "1";
		$rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$this->table, $where, null, $orderby,$limit);

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rs);
		$res = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$res->loadFromLocalAndForeignUid($row['uid_local'],$row['uid_foreign']);
		return $res;
	}


	/**
	 * Increase the sorting of the current relation
	 **/
	public function moveUp(){
		self::swapRelationSorting($this,$this->getPrevious());
	}

	public function moveDown(){
		self::swapRelationSorting($this,$this->getNext());
	}

	/**
	 * Method that swaps to relations of elements. The to relations need to have the same pid
	 */
	static function swapRelationSorting($relation1, $relation2){
		$temp = $relation1->getSorting();

		if($relation1->getLevel() == $relation2->getLevel()){
			$relation1->setSorting($relation2->getSorting());
			$relation2->setSorting($temp);
			$relation1->save();
			$relation2->save();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_SelectionContentelementRelationModel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/model/class.tx_bridgelib_SelectionContentelementRelationModel.php']);
}
?>