<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_bridge_lib_selection=1
');

if (!defined ('PATH_txbridge_lib')) {
	define('PATH_txbridge_lib', t3lib_extMgm::extPath('bridge_lib'));
}
?>