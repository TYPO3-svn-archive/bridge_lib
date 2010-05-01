<?php
class tx_bridgelib_ExporthandlerFactory{

	/**
	 * Factory method for exporthandlers
	 *
	 * @param string key of the export handler: crawlexport/directexport
	 */
	public static function getInstance($type){

		switch ($type){
			case 'directexport':
				return t3lib_div::makeInstance('tx_bridgelib_DirectExporthandler');
				break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_ExporthandlerFactory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_ExporthandlerFactory.php']);
}
?>