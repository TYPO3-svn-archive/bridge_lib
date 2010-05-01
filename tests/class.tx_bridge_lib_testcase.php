<?php

require_once (t3lib_extMgm::extPath('bridge_lib').'lib/misc/class.tx_bridgelib_Configurator.php');

require_once (t3lib_extMgm::extPath('bridge_lib').'lib/model/class.tx_bridgelib_AbstractDbmodel.php');
require_once (t3lib_extMgm::extPath('bridge_lib').'lib/model/class.tx_bridgelib_ContentelementModel.php');
require_once (t3lib_extMgm::extPath('bridge_lib').'lib/model/class.tx_bridgelib_SelectionModel.php');
require_once (t3lib_extMgm::extPath('bridge_lib').'lib/model/class.tx_bridgelib_SelectionContentelementRelationModel.php');

//INCLUDE export handling classes
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/export/class.tx_bridgelib_AbstractExporthandler.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/export/class.tx_bridgelib_DirectExporthandler.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/export/class.tx_bridgelib_ExporthandlerFactory.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/export/class.tx_bridgelib_Tocbuilder.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/export/class.tx_bridgelib_Xmlexport.php');

//INCLUDE transformation handling classes
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_AbstractTransformer.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_XslTransformer.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_PdflatexTransformer.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_ZipTransformer.php');

require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_Transformationflow.php');
require_once(t3lib_extMgm::extPath('bridge_lib').'lib/trafo/class.tx_bridgelib_TransformationflowFactory.php');

class tx_bridgelib_testcase extends tx_phpunit_testcase {
	private $now;
	private $rootpage_uid;
	
	public function __construct(){
		$this->now = time();
		$this->rootpage_uid = 1;
	}
	
	/**
	* The setUp Method is used to create some Fixture-objects to setup the environment for the tests.
	* 
	*/
	public function setUp(){
		$this->createFixturePage(99999);
		$this->createFixtureContentelementOnFixturePage(99999,99999);
		$this->createFixtureContentelementOnFixturePage(99998,99999);
		$this->createFixturePrintselection(99999,$this->now);	
	}
	
	/**
	* The tearDown Method is used to reset the environment for the tests.
	*/
	public function tearDown(){
		$this->deleteFixtureContentelement(99999);
		$this->deleteFixtureContentelement(99998);
		$this->deleteFixturePrintselection(99999);
		$this->deleteFixturePage(99999);
	}
	
	/**
	* This test checks if the contentelement_model delivers the correct header for 
	* the fixture object, which has been created in the setUp method.
	* 
	*/
	public function testContentelementModelCorrectFixtureHeader(){
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);
		
		self::assertTrue($celement->getHeader() == 'Fixture element 99999');
	}
	
	/**
	* This test checks the delivered header against an incorrect header.
	*/	
	public function testContentelementModelIncorrectFixtureHeader(){
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		self::assertFalse($celement->getHeader() == 'incorrect header');
	}

	/**
	* This test checks the delivered name of the selction_model.
	*/
	public function testSelectionModelCorrectFixtureName(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		self::assertTrue($selection->getName() == $this->now);
	}

	/**
	* This test creates an instance of the selection_model and the contentelement_model and adds
	* the selection_model to the contentelement_model and checks if the selection has really an assigend
	* contentelement.
	*/	
	public function testSelectionModelAddToSelection(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();
		
		$celements = $selection->getAllAssignedContentelements();
		$numelements  = count($celements);	
		
		$relation->delete();
		
		self::assertTrue($numelements  == 1);		
	}

	/**
	* This tests create two instances of the contentelement_model and one instance
	* of the selection_model. It assignes the two instances to the selection_model
	* and checks if the selections has two assigend contentelements.
	*/	
	public function testSelectionModelAddToSelectionMultipleContentelements(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());
		$relation2->save();
		
		$celements = $selection->getAllAssignedContentelements();	
		$numelements = count($celements);
		
		$relation->delete();
		$relation2->delete();
		
		self::assertTrue($numelements  == 2);
	}

	/**
	* This test creates to contentelements and one selection and add's the two contentelements 
	* to the selection. The second relation is a child of the first relation.
	* After the assignment of the as a child, the level of the relation will be checked.
	* The level in this case should be 1.
	*
	*/	
	public function testSelectionModelAddToSelectionMultipleTestLevel(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());		
		
		//set the uid of the first relation as pid of the second, so the second is a child of the first
		$relation2->setPid($relation->getUid());
		$relation2->save();
		
		$level = $relation2->getLevel();
		
		$relation->delete();
		$relation2->delete();
				
		self::assertTrue($level == 1);			
	}

	/**
	* This test assignes two contentelement to a selection. The second relation is assigned as child to the 
	* first relation. After the relation has been saved, the parent will be reassigned to the rootlevel.
	* After that the level will be checked if it is 0, as expected.
	*/	
	public function testSelectionModelAddToSelectionMultipleTestLevelReassign(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());
		
		
		//set the uid of the first relation as pid of the second, so the second is a child of the first
		$relation2->setPid($relation->getUid());
		$relation2->save();
		//now move it back to the rootlevel and save
		$relation2->setPid(0);
		$relation2->save();
					
		$level = $relation2->getLevel();
		
		$relation->delete();
		$relation2->delete();
				
		self::assertTrue($level == 0);			
	}	

	/**
	* This test checks the transformationflow_factory if it returns the correct number of transformationflows
	* configured in the fake_tsconfig.
	*/	
	public function testTransformationFlowFactory(){
		$factory = t3lib_div::makeInstance('tx_bridgelib_TransformationflowFactory');
		$factory->configure($this->getFixtureConfigurator());
		
		$avb_flows = $factory->getAvailableTransformationFlows();
		$num_flows = count($avb_flows);	
		
		self::assertTrue($num_flows == 2);
	}

	/**
	* This test creates a selection, two contentelements and assign them to the selection.
	* 
	*/	
	public function testExport(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());

		
		//set the uid of the first relation as pid of the second, so the second is a child of the first
		$relation2->setPid($relation->getUid());
		$relation2->save();

		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getFixtureConfigurator());
		$export->setPagetype(555);
		$export->setHost(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
		$export->setBaseScript('index.php');
		$export->setSelection($selection);
		
		$celems = $selection->getAllAssignedContentElements();
 		//export all contentelements of the selection
		foreach($celems as $celem){
			$export->setContentelement($celem);
			$export->writeExport();
		}
		
		$content1 = '';
		$content2 = '';
				
		//now check if the exportfile has been written
		$content1 = file_get_contents(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now."/xml/99999.xml"));
		$content2 = file_get_contents(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now."/xml/99998.xml"));
		
		$relation->delete();
		$relation2->delete();		
		
		$this->cleandirectory(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now));
		
		self::assertTrue(($content1 != '')&&($content2 != ''));	
	}

	/**
	* This test creates two instances of the contentelement_model and one of the selection_model.
	* The two contentelements will be assigned to the selection.
	* 
	*/	
	public function testExportAndTocBuilder(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());
		
		
		//set the uid of the first relation as pid of the second, so the second is a child of the first
		$relation2->setPid($relation->getUid());
		$relation2->save();

		//build the export
		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getFixtureConfigurator());
		$export->setPagetype(555);
		$export->setHost(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
		$export->setBaseScript('index.php');
		$export->setSelection($selection);
		
		$celems = $selection->getAllAssignedContentElements();
 		//export all contentelements of the selection
		foreach($celems as $celem){
			$export->setContentelement($celem);
			$export->writeExport();
		}

		//now build the toc
		$toc = t3lib_div::makeInstance('tx_bridgelib_Tocbuilder');
		$toc->configure($this->getFixtureConfigurator());
		$toc->setExportDir($selection->getNameForStorage());
		$toc->buildFromSelection($selection);
		$toc->save();
		
		//fetch the toc.xml content
		$content = '';		
		$content = file_get_contents(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now."/toc.xml"));
		
		//do cleanup
		$relation->delete();
		$relation2->delete();		
		
		$this->cleandirectory(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now));
		
		self::assertTrue($content != '','the toc.xml does not exist but it should exist.');	
	}

	/**
	*
	*/	
	public function testExportTocBuilderAndTransformationFlow(){
		$selection = t3lib_div::makeInstance('tx_bridgelib_SelectionModel');
		$selection->loadFromUid(99999);
		
		$celement = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement->loadFromUid(99999);	
		
		$relation = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation->setContentelementUid($celement->getUid());
		$relation->setSelectionUid($selection->getUid());
		$relation->save();

		$celement2 = t3lib_div::makeInstance('tx_bridgelib_ContentelementModel');
		$celement2->loadFromUid(99998);			

		$relation2 = t3lib_div::makeInstance('tx_bridgelib_SelectionContentelementRelationModel');
		$relation2->setContentelementUid($celement2->getUid());
		$relation2->setSelectionUid($selection->getUid());
		
		
		//set the uid of the first relation as pid of the second, so the second is a child of the first
		$relation2->setPid($relation->getUid());
		$relation2->save();

		//build the export
		$export = tx_bridgelib_ExporthandlerFactory::getInstance('directexport');
		$export->configure($this->getFixtureConfigurator());
		$export->setPagetype(555);
		$export->setHost(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
		$export->setBaseScript('index.php');
		$export->setSelection($selection);
		
		$celems = $selection->getAllAssignedContentElements();
 		//export all contentelements of the selection
		foreach($celems as $celem){
			$export->setContentelement($celem);
			$export->writeExport();
		}

		//now build the toc
		$toc = t3lib_div::makeInstance('tx_bridgelib_Tocbuilder');
		$toc->configure($this->getFixtureConfigurator());
		$toc->setExportDir($selection->getNameForStorage());
		$toc->buildFromSelection($selection);
		$toc->save();
		
		
		$flow_factory = t3lib_div::makeInstance('tx_bridgelib_TransformationflowFactory');
		$flow_factory->configure($this->getFixtureConfigurator());
		$flow = $flow_factory->buildTransformationFlowFromTypoScript('test',$selection->getNameForStorage());
		$flow->transform();
		
		//fetch the toc.xml content
		$content = '';		
		$content = file_get_contents(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now."/export.pdf"));
		
		//do cleanup
		$relation->delete();
		$relation2->delete();				
		$this->cleandirectory(t3lib_div::getFileAbsFileName("fileadmin/xmlexport/".$this->now));
		
		self::assertTrue($content != '','error during transformation');	
	}

	/**
	* Helper Method to generate a fixture configurator. With configurationvalues for
	* the unit test.
	*/
	private function getFixtureConfigurator(){
		$fake_localconf = array();
		$fake_localconf['flow_tspath'] = 'EXT:bridge_lib/tests/res/test.txt';
		$fake_localconf['xml_type'] = 555;
		$fake_localconf['xsl_path'] = 'EXT:bridge_lib/res/xslt/';
		$fake_localconf['xml_source_path'] = 'fileadmin/xmlexport/';
		$fake_localconf['color_profile_path'] = 'EXT:bridge_lib/res/icc/';

		$configurator = t3lib_div::makeInstance('tx_bridgelib_Configurator');
		$configurator->setLocalconf($fake_localconf);
		
		return $configurator;
	}
	
	/**
	* Helper Method to generate a fixture page, by a given uid.
	* 
	* @param int uid
	*/
	private function createFixturePage($uid){
		$row['uid'] 	= $uid;
		$row['pid']		= $this->rootpage_uid;
		$row['title']	= 'Testpage';
		
		$GLOBALS['TYPO3_DB']->exec_insertQuery('pages',$row);
	}	
	/**
	* This Method is used to create 
	*/
	private function createFixtureContentelementOnFixturePage($uid,$pid){	
		$row['uid']	  		= $uid;
		$row['pid']			= $pid;
		$row['header'] 		= 'Fixture element '.$uid;
		$row['ctype'] 		= 'textpic';
		$row['bodytext']	= '<b>This is bold Text</b>';	
		$GLOBALS['TYPO3_DB']->exec_insertQuery('tt_content',$row);
	}

	private function createFixturePrintselection($uid,$name){
		$row['uid']		= $uid;
		$row['name']	= $name;		
		$GLOBALS['TYPO3_DB']->exec_insertQuery('tx_bridge_lib_selection',$row);
	}
	
	private function deleteFixturePrintselection($uid){
		$where = 'uid='.$uid;		
		$GLOBALS['TYPO3_DB']->exec_deleteQuery('tx_bridge_lib_selection',$where);
	}
	
	private function deleteFixtureContentelement($uid){
		$where = 'uid='.$uid;		
		$GLOBALS['TYPO3_DB']->exec_deleteQuery('tt_content',$where);
	}
	
	private function deleteFixturePage($uid){
		$where = 'uid='.$uid;
		$GLOBALS['TYPO3_DB']->exec_deleteQuery('pages',$where);
	}
	
	private function cleandirectory($directory) {
    	if(!$handle = @opendir($directory)){
    		return;
    	}
    	while ($item = readdir($handle)) {
    		//skip current directory and base directory
        	if($item=='.' || $item=='..') continue;

        	//follow subdirectorys
        	if (is_dir($directory.'/'.$item)){
        		$this->cleandirectory($directory.'/'.$item);
        	}else{
        		//is file
        		@unlink($directory.'/'.$item);
        	}
    	}
	    closedir($handle);
    	@rmdir($directory);
	}
}

?>