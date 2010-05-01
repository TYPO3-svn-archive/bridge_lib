<?php
/**
 * This class is used to create a direct export of contentelement from the typo3 frontend
 */
class tx_bridgelib_DirectExporthandler extends tx_bridgelib_AbstractExporthandler{
	protected $pagetype;
	protected $host;
	protected $basescript;

	
	/**
	* Method to set the pagetype for the frontend where the contentelements are rendert as XML.
	* 
	* @param int pagetype
	*/
	public function setPagetype($type){
		$this->pagetype = $type;
	}
	
	/**
	* Set the hostname, where the frontend output should be requested from
	* 
	* @param string hostname (example: http://www.treyez.de)
	*/
	public function setHost($host){
		$this->host = $host;
	}

	/**
	* Methode to set the basescript, this should normally be 'index.php'
	* 
	* @param string basecript
	*/
	public function setBaseScript($basescript){
		$this->basescript = $basescript;
	}

	/**
	* Method to get the url where the contentelement should be found as XML.
	* 
	* @return string url
	*/
	private function getUrl(){
		//get pid of the current contentelement to get the url where is visible
		$pid = $this->celement->getPid();
		$query = '?id='.$pid.'&type='.$this->pagetype;
		$url = $this->host.$file.$query;
		return $url;
	}

	/**
	 * Concrete method that handles the direct export.
	 */
	protected function handleExport(){
		//fetch the content of the TYPO3-Fontend XML-Output
		$content = t3lib_div::getURL($this->getUrl());

		//get the sotragename of the collection
		$exportname = $this->selection->getNameForStorage();

		//create an XML-Exporter Object
		$xmlexport_class = t3lib_div::makeInstanceClassName('tx_bridgelib_Xmlexport');
		$xml_export = new $xmlexport_class($exportname,$this->configurator);

		//just export the current assigned contentelement
		$uids = array($this->celement->getUid());

		//Let the export export the pages i with the uids inside the uids array
		//from the content of the variable content (the xml frontent output of the page)
		$xml_export->export($content,$uids);
	}


	public function writeExport(){
		$this->handleExport();
	}

	/**
	 * Method to delete the assigend contentement of the selection from the export.
	 *
	 * @param void
	 */
	public function deleteExport(){
		$exportname = $this->selection->getNameForStorage();
		$uids = array($this->celement->getUid());

		//create an XML-Exporter Object
		$xmlexport_class = t3lib_div::makeInstanceClassName('tx_bridgelib_Xmlexport');
		$xml_export = new $xmlexport_class($exportname,$this->configurator);
		$xml_export->delete($uids);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_DirectExporthandler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_DirectExporthandler.php']);
}
?>