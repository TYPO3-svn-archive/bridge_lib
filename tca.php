<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_bridge_lib_selection"] = array (
	"ctrl" => $TCA["tx_bridge_lib_selection"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,celements,description"
	),
	"feInterface" => $TCA["tx_bridge_lib_selection"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_bridge_lib_selection',
				'foreign_table_where' => 'AND tx_bridge_lib_selection.pid=###CURRENT_PID### AND tx_bridge_lib_selection.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:bridge_lib/locallang_db.xml:tx_bridge_lib_selection.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),

		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:bridge_lib/locallang_db.xml:tx_bridge_lib_selection.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, celements, description")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>