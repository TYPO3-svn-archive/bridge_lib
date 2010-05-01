<?php
class tx_bridgelib_Configurator{
	private $localconf;
	
	public function setLocalconf($localconf){
		$this->localconf = $localconf;
	}
	public function getLocalconf(){
		return $this->localconf;	
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/misc/class.tx_bridgelib_Configurator.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/misc/class.tx_bridgelib_Configurator.php']);
}
?>