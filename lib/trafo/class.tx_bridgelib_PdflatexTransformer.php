<?php
class tx_bridgelib_PdflatexTransformer extends tx_bridgelib_AbstractTransformer{

	/**
	 * This method implements the transformation from a tex file to a pdf file.
	 * Internally it uses the commandline tool pdflatex to convert the tex file into
	 * a pdf file.
	 *
	 * @param void
	 * @return void
	 */
	public function transform(){
		$this->execWrapper("TEXINPUTS=:".$this->workingdir."; export TEXINPUTS; pdflatex -interaction=nonstopmode -output-directory=".$this->workingdir." ".$this->workingdir.$this->source,3);
		//second run to create latex toc
		$this->execWrapper("TEXINPUTS=:".$this->workingdir."; export TEXINPUTS; pdflatex -interaction=nonstopmode -output-directory=".$this->workingdir." ".$this->workingdir.$this->source,3);
		$this->execWrapper("TEXINPUTS=:".$this->workingdir."; export TEXINPUTS; pdflatex -interaction=nonstopmode -output-directory=".$this->workingdir." ".$this->workingdir.$this->source,3);
		$this->execWrapper("TEXINPUTS=:".$this->workingdir."; export TEXINPUTS; pdflatex -interaction=nonstopmode -output-directory=".$this->workingdir." ".$this->workingdir.$this->source,3);

		//replace the document root ending with trailing slash with emtpy string tp get the relative path to the export
		$path = str_replace(t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/','',$this->workingdir);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_PdflatexTransformer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bridge_lib/lib/trafo/class.tx_bridgelib_PdflatexTransformer.php']);
}
?>