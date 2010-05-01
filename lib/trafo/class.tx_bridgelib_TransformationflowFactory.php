<?php
/**
 * This class is an object factory for transformationflows.
 * Each build function returns a configured transformation_flow
 *
 * The transformationflow can be started the following way:
 *
 * $flow = tx_bridge_lib_transformationflow_factory::buildTransformationFlowFromTypoScript($conf,'foo','fileadmin/xmlexport/');
 * $flow->transform();
 *
 * In the example above a the flow 'foo' will be created base on the configuration in $conf(typosript configuration)
 * the working directory will be appended to each working direktory of the flow.
 *
 */
class tx_bridgelib_TransformationflowFactory{

	/**
	 * This Method is used to build an TransformationFlow from TypoScriptSetup
	 *
	 * The following example show the configuration of xml -> tex -> pdf transformation:
	 *
	 * transformationflows{
	 *		20{
	 *			name = latex
	 *			transformators{
	 *				10{
	 *					classname=tx_bridge_lib_xsltransformer
	 *					params{
	 *						source=toc.xml
	 *						target=export.tex
	 *						xslpath=EXT:bridge_lib/res/xslt/
	 *						xsl=latex.xsl
	 *						processor=salbatron
	 *						workingdirectory=fileadmin/xmlexport/
	 *					}
	 *				}
	 *				20{
	 *					classname=tx_bridge_lib_pdflatextransformer
	 *					params{
	 *						source=export.tex
	 *						target=export.pdf
	 *						workingdirectory=fileadmin/xmlexport/
	 *
	 *					}
	 *				}
	 *			}
	 *		}
	 *	}
	 */
	public function buildTransformationFlowFromTypoScript($key,$export_dir){
		if(!isset($key)) die('no tranformationflow key set');

		$tsconfig = $this->getTSConf();

		foreach($tsconfig['transformationflows.'] as $transformationflow){
			if($key == $transformationflow['name']){
				$flow = t3lib_div::makeInstance('tx_bridgelib_Transformationflow');
				$i = 0;
				foreach($transformationflow['transformators.'] as $trafoconf){
					$i++;
					$trafo = t3lib_div::makeInstance($trafoconf['classname']);
					self::applySettersOnTransformator($trafo,$trafoconf['params.']);

					//extend the working dir with the export dir of the current export
					$trafo->setWorkingDirectory($trafo->getWorkingDirectory().$export_dir.'/');

					$flow->addTransformator($i,$trafo);
				}

				return $flow;
			}
		}
	}

	/**
	 * Private helper method to apply setters on each transformation.
	 * Each item in the params part of the transformation setup will be
	 * delegated to a setter method with the same name:
	 *
	 * Example:
	 *
	 *	transformators{
	 *				10{
	 *					classname=tx_bridge_lib_xsltransformer
	 *					params{
	 *						source=toc.xml
	 *						target=export.tex
	 *						xslpath=EXT:bridge_lib/res/xslt/
	 *						xsl=latex.xsl
	 *						processor=salbatron
	 *						workingdirectory=fileadmin/xmlexport/
	 *					}
	 *				}
	 * 	}
	 *
	 *   In this case the method will call
	 *
	 *   $trafo->setSource("toc.xml")
	 *   $trafo->setTarget("export.tex");
	 *   ...
	 *
	 * @param object a trafo object
	 * @prams array with params which should be setted for this transformator
	 *
	 */
	private function applySettersOnTransformator($trafo,$params){
		foreach($params as $paramkey => $param){
			//remove point from param key in case of arrays
			if(strpos($paramkey,'.') > 0){
				$paramkey = substr($paramkey,0,strpos($paramkey,'.'));
			}

			//Build the name of the Settermethod  from the key of the parameter
			$method = 'set'.$paramkey;
			if(method_exists($trafo,$method)){
				//apply settermethod on the transformator
				$trafo->$method($param);
			}else{
				die('trafo does not support method '.$method);
			}
		}
	}
	
	public function configure($configurator){
		$this->configurator = $configurator;
	}
	
	/**
	 * Public helper method which returns the available transformationflows
	 * from an TypoScript Setup of the TransformationFlow.
	 */
	public function getAvailableTransformationFlows(){
		$tsconfig = $this->getTSConf();

		foreach($tsconfig['transformationflows.'] as $transformationflow){
			$res[] = $transformationflow['name'];
		}

		return $res;
	}

	/**
	 * Method to get the TypoScript configuration array
	 *
	 * @param void
	 * @return array
	 */
	private function getTSConf(){
		$localconf = $this->getLocalconf();
		$flowts_configfile = $localconf['flow_tspath'];
		$tsfile_content = $this->getTSFileContent($flowts_configfile);
		$tsconf = $this->buildTSConf($tsfile_content);

		return $tsconf;
	}
	
	/**
	 * Method to get the localconf-array.
	 *
	 * @param string optional confpath for testing
	 * @return array array with configurations
	 */
	private function getLocalconf(){
		return $this->configurator->getLocalconf();
	}

	/**
	 * Private Method to create the TypoScript array from the TypoScript filecontent.
	 *
	 * @param string typoscript
	 * @return array array with typoscript setup
	 */
	private function buildTSConf($tsfile_content){
		$tsparser = t3lib_div::makeInstance('t3lib_TSparser');
		$tsparser->parse($tsfile_content);
		$ts_array = $tsparser->setup;

		return $ts_array;
	}

	/**
	 * Method to get the filecontent of the typoscript setup.
	 *
	 * @param string filename
	 * @param string filecontent
	 */
	private function getTSFileContent($filename){
		$abs_filename = t3lib_div::getFileAbsFileName($filename);

		return file_get_contents($abs_filename);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_TransformationflowFactory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_TransformationflowFactory.php']);
}
?>