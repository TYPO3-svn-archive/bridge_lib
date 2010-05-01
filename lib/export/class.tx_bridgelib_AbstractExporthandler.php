<?php
abstract class tx_bridgelib_AbstractExporthandler{
	protected $selection;
	protected $celement;
	protected $relation;
	protected $configurator;
	
	public function configure($configurator){
		$this->configurator = $configurator;
	} 
	/**
	 * Set the subject to export, can be a selection or a single document
	 *
	 * @param tx_bridge_lib_selection_model
	 **/
	public function setSelection(tx_bridgelib_SelectionModel $selection){
		$this->selection = $selection;
		//try to create the relation object between ContentElement and Selection
		$this->createRelation();
	}

	/**
	 * Method to set the contentelement that should be exported
	 *
	 * @param tx_bridge_lib_contentelement_model contentelement.
	 */
	public function setContentelement(tx_bridgelib_ContentelementModel $celement){
		$this->celement = $celement;
		//try to create the relation object between ContentElement and Selection
		$this->createRelation();
	}

	/**
	 * Method is used to load the relation object, which exists between the
	 * contentelement and the selection. This relation should be exported.
	 */
	private function createRelation(){
		if(isset($this->celement) && isset($this->selection)){
			$this->relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
			$this->relation->loadFromLocalAndForeignUid($this->selection->getUid(),$this->celement->getUid());
		}
	}

	/**
	 * Abstract method that should be implemented by an Exporthandler to do the export
	 * of the relation.
	 *
	 * @param void
	 */
	abstract protected function handleExport();

	abstract public function writeExport();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_AbstractExporthandler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_AbstractExporthandler.php']);
}

?>