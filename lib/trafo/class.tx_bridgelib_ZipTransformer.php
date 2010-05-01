<?php
class tx_bridgelib_ZipTransformer extends tx_bridgelib_AbstractTransformer{
	private $type;

	/**
	 * Method to set the type of the transformation
	 */
	public function setType($type){
		$this->type = $type;
	}

	/**
	 * Implements the interface to apply the transformation
	 */
	public function transform(){
		switch($this->type){
			case 'tgz':
				$this->runTGZ();
				break;
		}
	}

	/**
	 * Zips the content with tar/gz
	 */
	private function runTGZ(){
		if(is_array($this->source)){
			foreach($this->source as $source)
			$allsources .= ' '.$source;
		}
		$target = $this->target;

		$cmd = "cd ".$this->workingdir."; tar czvf ".$target.$allsources;
		$this->execWrapper($cmd,2);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_ZipTransformer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_ZipTransformer.php']);
}
?>