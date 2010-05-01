<?php
class tx_bridgelib_XslTransformer extends tx_bridgelib_AbstractTransformer{
	private $xsl_file;
	private $xsl_path;
	private $processor;
	private $debug = true;

	/**
	 * Method to set the path where XSL Stylesheets are stored.
	 *
	 */
	public function setXSLPath($path){
		$this->xsl_path = t3lib_div::getFileAbsFileName($path);
	}

	/**
	 * Method to get an array with all xsl files stored in the xsl_path
	 */
	public function getXSLFiles(){
		if(isset($this->xsl_path)){
			$files = t3lib_div::getFilesInDir($this->xsl_path,'xsl');
			return $files;
		}else{
			//error: no xsl_path set
		}
	}

	/**
	 * Method to set the XSL File for the xsl transfom
	 */
	public function setXSL($xsl){
		$this->xsl_file = $xsl;
	}

	/**
	 * Method to set the XSLProcessor that should be used.
	 *
	 * @param string name 'saxon' / 'salbatron'
	 */
	public function setProcessor($name){
		$this->processor = $name;
	}

	public function setSaxonCommand($command){
		$this->saxon_command = $command;
	}

	/**
	 * Implementation of the interface method transform. Depending on the configured processor,
	 * it will be executed.
	 *
	 * @param void
	 * @return void
	 */
	public function transform(){
		switch($this->processor){
			case 'saxon':
				$this->runSaxon();
				break;

			case 'salbotron':
			default:
				$this->runSalbatron();
				break;
		}
	}

	/**
	 * Method to run the Salbatron XSLT Processor
	 *
	 */
	private function runSalbatron(){

		ob_start();
		//load the xsl stylesheet as domDocument
		$xslt_doc = new domDocument();
		$file = $this->xsl_path.'/'.$this->xsl_file;
		$xslt_doc->load($file);

		//load the source XML Document as domDocument
		$xml_doc = new domDocument();
		$abs_source = $this->workingdir.'/'.$this->source;
		$xml_doc->load($abs_source);

		$proc = new xsltprocessor;
		$xsl = $proc->importStylesheet($xslt_doc);

		t3lib_div::writeFile($this->workingdir.$this->target,$proc->transformToXml($xml_doc));
		$res = ob_get_contents();
		ob_end_clean();

		if ($this->debug) t3lib_div::devLog ('XSL Trafo: '.$res,'tx_bridge_lib');
	}

	/**
	 * Method to run the Saxon XSLT Processor
	 *
	 * @todo exlude path
	 */
	private function runSaxon(){
		$this->execWrapper(	$this->saxon_command.
							" -o:".$this->workingdir.$this->target.
							" -xsl:".$this->xsl_path.$this->xsl_file.
							" -s:".$this->workingdir.$this->source);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_XslTransformer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_XslTransformer.php']);
}
?>