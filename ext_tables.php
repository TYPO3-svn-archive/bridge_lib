<?php
t3lib_extMgm::addToInsertRecords('tx_bridge_lib_selection');

$TCA["tx_bridge_lib_selection"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:bridge_lib/locallang_db.xml:tx_bridge_lib_selection',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_bridge_lib_selection.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, name, description",
	)
);

$tempColumns = Array (
	"tx_bridge_lib_printimages" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:bridge_lib/locallang_db.xml:tt_content.tx_bridge_lib_printimages",
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],
			"max_size" => 30000,
			"uploadfolder" => "uploads/tx_bridgelib",
			"size" => 5,
			"minitems" => 0,
			"maxitems" => 30,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_bridge_lib_printimages;;;;1-1-1");
?>