<?php
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');

class tx_bridgelib_Tocbuilder{
	private $toc;

	public function __construct(){
		global $FILEMOUNTS, $TYPO3_CONF_VARS, $BE_USER;

		$this->fileProcessor = t3lib_div::makeInstance('t3lib_extFileFunctions');
		$this->fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS['BE']['fileExtensions']);
		$this->fileProcessor->init_actionPerms($BE_USER->getFileoperationPermissions());

		$this->initEmptyToc();
	}
	
	public function configure($configurator){
		$this->localconf = $configurator->getLocalconf();
		$this->file_storage_dir = $this->localconf['xml_source_path'];
	}

	/**
	 * Builds a toc from a selection.
	 *
	 * @param object selection
	 */
	public function buildFromSelection($selection){
		$this->visitSelectionRelations($selection,0);
	}

	/**
	 * Visits all nodes off a selection and adds them to the toc.
	 *
	 * @param object selection
	 * @param int pid
	 */
	private function visitSelectionRelations($selection, $pid){
		$relations = $selection->getRelations($pid,true);
		foreach($relations as $relation){
			$celem = $relation->getContentElement();

			if($relation->getPid() != 0){
				$parent = $relation->getParent();
				$parent_element = $parent->getContentElement();
			}

			$this->addContentElement($celem,$relation->getLevel(),$parent_element);
			$this->visitSelectionRelations($selection,$relation->getUid());
		}
	}

	/**
	 * Method to set the Storagedirectory of the toc builder
	 *
	 * @param string path
	 */
	public function setExportDir($dir){
		$this->file_storage_dir .= $dir;
	}

	/**
	 * Method to get the toc XML Content
	 */
	public function getTocXML(){
		return $this->toc->saveXML();
	}

	/**
	 * Method to save the toc in the export directory
	 */
	public function save(){
		$this->exportContent($this->toc->saveXML(),'toc.xml',$this->getAbsStorageDir());
	}

	/**
	 * Method to add a contentelement to the toc
	 *
	 * @param tx_bridge_lib_contentelement_model contentelement
	 * @param int level
	 */
	public function addContentElement(tx_bridgelib_ContentelementModel $celem,$level,$parent = 0){
		//create a new row
		$row = $this->toc->createElement('tt:elem');
		$row->setAttribute("uid",$celem->getUid());
		$row->setAttribute("level",$level);
		$row->setAttribute("href",'xml/'.$celem->getUid().".xml");

		if(!is_object($parent)){
			$this->toc->documentElement->appendChild($row);
		}else{
			//in this case getElementById returns an element with the uid, bescause uid is defined as ID in the dtd
			$element = $this->toc->getElementById($parent->getUid());
			$element->appendChild($row);
		}
	}

	/**
	 * Method to initialize the internal toc object
	 *
	 * @param void
	 * @return void
	 */
	private function initEmptyToc(){
		$this->toc = new DOMDocument();
		$this->toc->loadXML($this->getXMLHeader().'<tt:toc xmlns:tt="http://notyetpublished"></tt:toc>');
	}

	/**
	 * writes a file to the storagedir
	 *
	 * @param string filecontent
	 * @param string filename
	 */
	private function exportContent($content,$filename=false,$path){
		if(!$filename){
			$filename = microtime(true).'.xml';
		}

		$cmd['newfile'][0] = array("data" => strtolower($filename),"target" => $path.'/');
		$this->fileProcessor->start($cmd);
		$this->fileProcessor->processData();

		$cmd['editfile'][1] = array("data" => $content, "target" => $path.'/'.strtolower($filename));
		$this->fileProcessor->start($cmd);
		$this->fileProcessor->processData();
	}

	/**
	 * Method to generate the xml header.
	 * 
	 * @return string
	 */
	private function getXMLHeader(){
		return '<?xml version="1.0" encoding="utf8" standalone="yes"?>
			<!DOCTYPE tt:toc [
			<!ENTITY lt     "&#38;#60;">
			<!ENTITY gt     "&#62;">
			<!ENTITY amp    "&#38;#38;">
			<!ENTITY apos   "&#39;">
			<!ENTITY quot   "&#34;">
			<!ENTITY nbsp   "&#160;">
		   	<!ATTLIST tt:elem
    			uid ID #IMPLIED
  			>
			]>';
	}

	/**
	 * Returns the Storagedir
	 *
	 * @access private
	 * @return string absolute path to the export directory
	 */
	private function getAbsStorageDir(){
		return t3lib_div::getFileAbsFileName($this->file_storage_dir);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_Tocbuilder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_Tocbuilder.php']);
}
?>