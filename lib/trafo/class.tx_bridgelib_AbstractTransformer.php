<?php
/**
 *
 *
 */
abstract class tx_bridgelib_AbstractTransformer{
	protected $source;
	protected $target;
	protected $workingdir;
	private $writedevlog = true;

	/**
	 * Method to set the Sourcefile of the transformator.
	 * The sourcefile will usually be concatenated with the workingdir on runtime
	 * to get the complete path of the sourcefile
	 *
	 * @param string source file
	 * @return void
	 */
	public function setSource($source){
		$this->source = $source;
	}

	/**
	 * Method to set the Targetfile of the transformator.
	 * The targetfile will usually be concatenated with the workingdir on runtime
	 * to get the complete path of the targetfile
	 *
	 * @param string target file
	 * @return void
	 */
	public function setTarget($target){
		$this->target = $target;
	}

	/**
	 * Method to set the workingdirectory of the transformator. The workingdirectory
	 * contains source and targetfiles of an transformator
	 *
	 * @param string working directory
	 * @return void
	 */
	public function setWorkingDirectory($dir){
		$this->workingdir = t3lib_div::getFileAbsFileName($dir);
	}

	/**
	 * Method to get the workingdirektory of the transformator. The workingdirectory
	 * contains source and targetfiles of an transformator
	 *
	 */
	public function getWorkingDirectory(){
		return $this->workingdir;
	}

	/**
	 * Every transformer should use this execWrapper to perform exec calls, because
	 * of securtiy reasons.
	 *
	 * @param string exec string
	 * @param int number of sequences in the exec string
	 */
	protected function execWrapper($exec,$sequences = 1){
		$exec = str_replace('..','',$exec);

		//each ; starts a sequens
		$counted_sequences = substr_count ($exec,';')+1;

		if($counted_sequences != $sequences){
			die('wrong sequence count in exec wrapper');
		}else{
			exec($exec);
		}
	}

	/**
	 * This method should be implemented by the transformer class. After the transform method
	 * has been performed the targetfile should contain the result of the transformation
	 *
	 * @param void
	 * @return void
	 */
	abstract public function transform();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_AbstractTransformer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_AbstractTransformer.php']);
}
?>