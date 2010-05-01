<?php
/**
 * Implementation of the transformation flow.
 * The TransformationFlow object simply calls the transform
 * Method of each registered tranformer in the correct order
 */
class tx_bridgelib_Transformationflow{
	private $transformators = array();
	private $results = array();

	/**
	 * Method to register a transformer in this flow.
	 *
	 * @param int order of the transformator in the flow
	 * @param object the transformer object
	 */
	public function addTransformator($order,$transformator){
		$this->transformators[$order] = $transformator;
	}

	/**
	 * The transform method performes the transform method on each registered
	 * transformer based on the order of registration.
	 *
	 * @param void
	 * @return void
	 */
	public function transform(){
		foreach($this->transformators as $transformator){
			$this->results[] = $transformator->transform();
		}
	}

	/**
	 * Method to return the result array of the transformation
	 */
	public function getResults(){
		return $this->results;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_Transformationflow.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_Transformationflow.php']);
}
?>