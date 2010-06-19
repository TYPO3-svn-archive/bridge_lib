<?php
/**
 * The abstract controller provides basic functionallity for controllers that want to use the
 * bridge_lib api.
 */
abstract class tx_bridgelib_AbstractController{

	/**
	 * Method to add a Contentelement to a printselection
	 *
	 * @param int uid of the contentelement
	 * @param int uid of the parent relation
	 * @param int sorting on the current level
	 */
	protected function addToSelection($uid,$pid,$sorting){
		//id of the secection
		$selection_uid = $this->getCurrentSelectionUid();

		if(isset($selection_uid) && isset($uid) && isset($pid)){
			if($selection_uid != 0){
				$model = self::createRelationModel();
				$model->setSelectionUid($selection_uid);
				$model->setContentElementUid($uid);
				$model->setPid($pid);
				$model->setSorting($sorting);
				$model->save();

				//return the uid of the new relation
				return $model->getUid();
			}
		}
	}

	/**
	 * Method to move a Contentelement in the printselection
	 *
	 * @param int uid of the relation that should be moved
	 * @param int pid of the relation
	 * @param int sorting sorting value on the level where the relation should be moved to
	 */
	protected function moveInSelection($uid,$pid,$sorting){
		//id of the secection
		$selection_uid = $this->getCurrentSelectionUid();

		if(isset($selection_uid) && isset($uid) && isset($pid)){
			if($selection_uid != 0){
				$model = self::createRelationModel();
				$model->loadFromUid($uid);
				$model->setPid($pid);
				$model->setSorting($sorting);
				$model->save();
			}
		}
	}

	/**
	 * Method to apply an transformation on the selected printselection
	 *
	 * @param string key of the transformationflow that should be applyed on the printselection
	 */
	protected function transformExport($flowkey){
		$selection = $this->getCurrentSelection();

		//build toc
		$toc = t3lib_div::makeInstance('tx_bridgelib_Tocbuilder');
		$toc->configure($this->getConfigurator());
		$toc->setExportDir($selection->getNameForStorage());
		$toc->buildFromSelection($selection);
		$toc->save();

		//depending on the selection of the user we load the required dataflow
		$flow_key = mysql_escape_string($flowkey);
		$factory = t3lib_div::makeInstance('tx_bridgelib_TransformationflowFactory');
		$factory->configure($this->getConfigurator());
		
		$transfo_flow = $factory->buildTransformationFlowFromTypoScript($flow_key,$selection->getNameForStorage());
		$transfo_flow->transform();
	}

	/**
	 * Method to export all Contentelements of the current selection
	 */
	protected function exportSelection(){
		$selection = $this->getCurrentSelection();
		
		//get all contentelements of the current selection
		$celems = $selection->getAllAssignedContentElements();

		//create the exporthandler
		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getConfigurator());
		$export->setSelection($selection);

 		//export all contentelements of the selection
		foreach($celems as $celem){
			$export->setContentelement($celem);
			$export->writeExport();
		}
	}

	/**
	 * Method to get an array with all available transformationflows.
	 *
	 * @param void
	 * @return array
	 */
	protected function getTransformationflows(){
		//get all available transformationflows from the factory
		//the factory parses the ts config file to get all available transformation flows
		$factory = t3lib_div::makeInstance('tx_bridgelib_TransformationflowFactory');
		$factory->configure($this->getConfigurator());
				
		$avb_flows = $factory->getAvailableTransformationFlows();
		
		

		//konvert the output structure into the input structure for the userinterface
		$res['flows'] = array();
		foreach($avb_flows as $flow){
			$res['flows'][] = array('key' => $flow, 'name' => $flow);
		}

		return $res;
	}

	/**
	 * Method to remove a contentelement from an export by a given uid
	 *
	 * @param int uid
	 */
	protected function removeFromExport($uid){
		$selection = $this->getCurrentSelection();
		$celem = $selection->getContentElementFromSelection($uid);

		//delete the exported file
		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getConfigurator());
		$export->setContentelement($celem);
		$export->setSelection($selection);
		$export->deleteExport();
	}

	/**
	 * Method to remove a contentelment from a selection.
	 *
	 * @param int uid
	 */
	protected function removeFromSelection($uid){
		$model = self::createRelationModel();
		$model->loadFromUid($uid);
		$model->delete();
	}
	
	/**
	 * Method to add a contentelement to the export.
	 *
	 * @param int uid
	 */
	protected function addToExport($uid){
		$selection = $this->getCurrentSelection();
		$celem = $selection->getContentElementFromSelection($uid);

		//export the contentelement, after the contentelement was added
		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getConfigurator());
		$export->setPagetype(555);
		$export->setHost(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
		$export->setBaseScript('index.php');
		$export->setContentelement($celem);
		$export->setSelection($selection);
		$export->writeExport();
	}

	/**
	 * Move a contentelement down on its the level.
	 *
	 * @param int uid.
	 */
	protected function moveDown($uid){
		$model = self::createRelationModel();
		$model->loadFromUid($uid);
		$model->moveDown();
		$model->save();
	}

	/**
	 * Move a contentlement up on its level
	 *
	 * @param int uid.
	 */
	protected function moveUp($uid){
		$model = self::createRelationModel();
		$model->loadFromUid($uid);
		$model->moveUp();
		$model->save();
	}

	/**
	 * Get the uid of the current selected selection stored in the
	 * user session.
	 *
	 * @param void
	 * @return int.
	 */
	protected function getCurrentSelectionUid(){
		global $BE_USER;
		return $BE_USER->getSessionData("selection_uid");
	}

	
	/**
	* Returns the current selection object initialized from the session uid of the current selection
	*/
	protected function getCurrentSelection(){
		$selection_uid = $this->getCurrentSelectionUid();
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid($selection_uid);	
		
		return $selection;
	}
	
	/**
	 * Store the id of a selection in the session.
	 *
	 * @param int selection_uid
	 */
	protected function setCurrentSelectionUid($uid){
		global $BE_USER;
		$BE_USER->setAndSaveSessionData("selection_uid",$uid);
	}
	
	/**
	 *
	 */
	protected function getSelections(){
		$res = array();
		$res['selections'] = array();

		$all_selections = tx_bridgelib_SelectionModel::loadAll();
		foreach($all_selections as $selection){
			$res['selections'][] = array('id' => $selection->getUid(),
                     'name' => $selection->getName());
		}

		return $res;
	}

	/**
	 * @return tx_bridge_lib_selection_contentlement_mm_model a new instance of the relation model
	 */
	protected function createRelationModel(){
		return t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
	}

	abstract public function handleRequest($key);
	
	protected function getConfigurator(){
		$configurator = t3lib_div::makeInstance('tx_bridgelib_Configurator');
		$configurator->setLocalconf(unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bridge_lib']));
		
		return $configurator;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/controller/class.tx_bridgelib_AbstractController.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/controller/class.tx_bridgelib_AbstractController.php']);
}
?>