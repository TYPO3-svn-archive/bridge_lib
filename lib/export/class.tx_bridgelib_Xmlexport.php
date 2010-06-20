<?php
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');

class tx_bridgelib_Xmlexport{
	private $file_storage_dir;
	private $export_file = 'content.xml';
	private $doc;
	private $export_uids;
	private $delete_uids;
	private $fileProcessor;
	private $color_profile_dir;

	public function __construct($export_dir,$configurator){
		global $FILEMOUNTS, $TYPO3_CONF_VARS, $BE_USER;

		$this->fileProcessor = t3lib_div::makeInstance('t3lib_extFileFunctions');
		$this->fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS['BE']['fileExtensions']);
		$this->fileProcessor->init_actionPerms($BE_USER->getFileoperationPermissions());

		$this->localconf = $configurator->getLocalconf();
		$this->file_storage_dir = $this->localconf['xml_source_path'];
		$this->color_profile_dir = $this->localconf['color_profile_path'];
		//array with uids for this pages that should be exported

		$this->createDir($this->getAbsStorageDir(),$export_dir);
		$this->setStorageDir($this->getAbsStorageDir().$export_dir);
		$this->createDir($this->getAbsStorageDir(),"xml");
	}

	/**
	 * The Export method will be called from the crawlexport frontend,
	 * this should normally be called by the typo3 crawler extension
	 *
	 * @access public
	 * @param string content of the current rendert page
	 * @param string subdirectory for the current export run
	 * @return void
	 */
	public function export($content,$export_uids){
		$this->export_uids = $export_uids;
		$this->doc = new DOMDocument();
		$this->doc->loadXML($content);

		//export images
		$this->exportImagesAndSetPath('image');
		$this->exportContentElements('content');
	}

	/**
	* Method to delete a set of contentelement from the export.
	* 
	* @param array array of uids that should be deleted
	*/
	public function delete($delete_uids){
		$this->delete_uids = $delete_uids;
		$this->deleteContentElements();
	}

	/**
	* Method to perform the delete operation from the delete method.
	*/
	private function deleteContentElements(){
		if(is_array($this->delete_uids)){
			foreach($this->delete_uids as $delete_uid){
				$cmd['delete'][0] = array("data" => $this->getAbsStorageDir()."/xml/".$delete_uid.".xml");
				$this->fileProcessor->start($cmd);
				$this->fileProcessor->processData();
			}
		}
	}

	/**
	 * Method to create the exportdirectory for the current export run
	 *
	 * @access private
	 * @param string name of the subdirectory
	 * @return void
	 */
	private function createDir($path,$dir){
		//ensure that the storagedir is created
		if(!is_dir($path."/".$dir)){
			$cmd['newfolder'][0] = array("data" => $dir, "target" => $path);
			
								
			$this->fileProcessor->start($cmd);
			var_dump($this->fileProcessor->processData());
		}
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

	/**
	 * Returns the Profiledir of the color profiles
	 *
	 * @access private
	 * @return string absolute path to the export directory
	 */
	private function getAbsProfileDir(){
		return t3lib_div::getFileAbsFileName($this->color_profile_dir);
	}

	/**
	 * setmethod for the storagedir
	 *
	 * @access private
	 * @param relativ path to the storagedir
	 * @return void
	 */
	private function setStorageDir($dir){
		$this->file_storage_dir = $dir;
	}

	/**
	 * writes a file to the storagedir
	 *
	 * @param string filecontent
	 * @param string filename
	 */
	public function exportContent($content,$filename=false,$path){
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
	 * This method extracts the tt_content nodes from the xml that should be exported.
	 * This is necessary becaus the frontend page normally contains more contentelements than these
	 * which should be exported.
	 * 
	 * @param string name of the tags for the contentelements
	 */
	private function exportContentElements($tt_content_nodes){
			
		foreach($this->doc->getElementsByTagName($tt_content_nodes) as $tt_content_node){

			$uid = $tt_content_node->getAttribute('uid');

			if($this->isExportableUid($uid)){
				$xml = $this->getXMLHeader().$this->doc->saveXML($tt_content_node);
				$this->exportContent($xml,$uid.".xml",$this->getAbsStorageDir()."/xml");
			}
		}
	}

	/**
	* Method to check if the requested uid should be exported or not
	* 
	* @param int uid
	* @return boolean
	*/
	private function isExportableUid($uid){
		return in_array($uid,$this->export_uids);
	}

	private function exportImagesAndSetPath($image_nodes){
		foreach($this->doc->getElementsByTagName($image_nodes) as $image){
			//get the href attribute of the href tag
			$img_loc = $image->getAttribute('href');
			$filename = substr($img_loc,strrpos($img_loc,"/")+1);
			if($filename != ''){

				$export= 'eps';

				//create the path to the image
				$parts = t3lib_div::split_fileref($img_loc);
				$this->createPath($parts['path']);
				$abs_img_loc = t3lib_div::getFileAbsFileName($img_loc);

				switch($export){
					case 'eps':
						//get the absolut image file location
						$eps_filename = strtolower(substr($img_loc,0,strrpos($img_loc,".")).".eps");
						$pdf_filename = strtolower(substr($img_loc,0,strrpos($img_loc,".")).".pdf");
						$jpg_filename = strtolower(substr($img_loc,0,strrpos($img_loc,".")).".jpg");

						###################################################################
						# THIS IS NEEDED IF THE IMAGES SHOULD BE CONVERTED FROM RGB TO CMYK
						###################################################################
						//@todo
						//$profile_rgb 	= $this->_getAbsProfileDir()."/rgb/sourceprofile.icc";
						//$profile_cmyk	= $this->_getAbsProfileDir()."/cmyk/targetprofile.icc";
						//exec("convert -profile ".$profile_rgb." -profile ".$profile_cmyk." ".$abs_img_loc." ".$this->_getAbsStorageDir()."/".$eps_filename);
						//exec("convert -profile ".$profile_rgb." -profile ".$profile_cmyk." ".$abs_img_loc." ".$this->_getAbsStorageDir()."/".$pdf_filename);
						//exec("convert -profile ".$profile_rgb." -profile ".$profile_cmyk." ".$abs_img_loc." ".$this->_getAbsStorageDir()."/".$jpg_filename);

						exec(escapeshellcmd  ("convert  ".$abs_img_loc."  ".$this->getAbsStorageDir()."/".$pdf_filename));
						exec(escapeshellcmd  ("convert  ".$abs_img_loc."  ".$this->getAbsStorageDir()."/".$eps_filename));
						exec(escapeshellcmd  ("convert  ".$abs_img_loc."  ".$this->getAbsStorageDir()."/".$jpg_filename));
						$image->setAttribute('href',strtolower($eps_filename));
						break;

					default:
						//fetch the filecontent
						$image_content = t3lib_div::getUrl($abs_img_loc);
						$this->exportContent($image_content,$img_loc);
						$image->setAttribute('href',strtolower($img_loc));
						break;
				}
			}
		}
	}

	/**
	 * Method to create a path inside the storagedirectory
	 *
	 * @param string path
	 */
	private function createPath($path){
		$directorys = explode("/",$path);
		$processed_path = $this->getAbsStorageDir();

		foreach($directorys as $directory){
			$cmd['newfolder'][0] = array("data" => $directory, "target" => $processed_path);
			$this->fileProcessor->start($cmd);
			$this->fileProcessor->processData();
			$processed_path = $processed_path."/".$directory;
		}
	}

	private function getXMLHeader(){
		return '<?xml version="1.0" encoding="utf8" standalone="yes"?>
			<!DOCTYPE page [
			<!ENTITY lt     "&#38;#60;">
			<!ENTITY gt     "&#62;">
			<!ENTITY amp    "&#38;#38;">
			<!ENTITY apos   "&#39;">
			<!ENTITY quot   "&#34;">
			<!ENTITY nbsp   "&#160;">
			]>';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_Xmlexport.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/export/class.tx_bridgelib_Xmlexport.php']);
}
?>