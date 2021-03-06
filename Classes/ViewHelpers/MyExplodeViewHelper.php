<?php
namespace Unima\Publist4ubma2\ViewHelpers;

/***************************************************************
*  Copyright notice
*
*  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Explode ViewHelper
 *
 * Explodes a string by $glue
 *
 * @package Typo3
 * @subpackage publist4ubma2
 */
class MyExplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var string
	 */
	protected $method = 'explode';

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('content', 'string', 'String to be exploded by glue)', FALSE, '');
		$this->registerArgument('glue', 'string', 'String used as glue in the string to be exploded. Use glue value of "constant:NAMEOFCONSTANT" (fx "constant:LF" for linefeed as glue)', FALSE, ',');
		$this->registerArgument('as', 'string', 'Template variable name to assign. If not specified returns the result array instead');
	}

	/**
	 * Render method
	 *
	 * @return mixed
	 */
	public function render() {
		$content = $this->arguments['content'];
		$as = $this->arguments['as'];
		$glue = $this->resolveGlue();
		$contentWasSource = FALSE;
		if (TRUE === empty($content)) {
			$content = $this->renderChildren();
			$contentWasSource = TRUE;
		}
		$output = call_user_func_array($this->method, array($glue, $content));
		if (TRUE === empty($as) || TRUE === $contentWasSource) {
			return $output;
		}
		$variables = array($as => $output);
		$content = $this->renderChildrenWithVariables($this, $this->templateVariableContainer, $variables);
		return $content;
	}

	/**
	 * Detects the proper glue string to use for implode/explode operation
	 *
	 * @return string
	 */
	protected function resolveGlue() {
		$glue = $this->arguments['glue'];
		if (FALSE !== strpos($glue, ':') && 1 < strlen($glue)) {
			// glue contains a special type identifier, resolve the actual glue
			list ($type, $value) = explode(':', $glue);
			switch ($type) {
				case 'constant':
					$glue = constant($value);
					break;
				default:
					$glue = $value;
			}
		}
		return $glue;
	}


	/**
	 * Renders tag content of ViewHelper and inserts variables
	 * in $variables into $variableContainer while keeping backups
	 * of each existing variable, restoring it after rendering.
	 * Returns the output of the renderChildren() method on $viewHelper.
	 *
	 + Copied from https://github.com/FluidTYPO3/vhs
	 */
//	private static function renderChildrenWithVariables(Tx_Fluid_Core_ViewHelper_AbstractViewHelper $viewHelper, Tx_Fluid_Core_ViewHelper_TemplateVariableContainer $variableContainer, array $variables) {
	private static function renderChildrenWithVariables(\TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper $viewHelper, \TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer, array $variables) {
		$backups = array();
		foreach ($variables as $variableName => $variableValue) {
			if (TRUE === $variableContainer->exists($variableName)) {
				$backups[$variableName] = $variableContainer->get($variableName);
				$variableContainer->remove($variableName);
			}
			$variableContainer->add($variableName, $variableValue);
		}
		$content = $viewHelper->renderChildren();
		foreach ($variables as $variableName => $variableValue) {
			$variableContainer->remove($variableName);
			if (TRUE === isset($backups[$variableName])) {
				$variableContainer->add($variableName, $variableValue);
			}
		}
		return $content;
	}
}


