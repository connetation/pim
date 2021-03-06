<?php

namespace CommerceTeam\Commerce\ViewHelpers\Be;

use \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use \TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * ViewHelper to generate a Backend-Link to edit a record
 *
 * @package TYPO3
 * @subpackage tx_commerce
 */
class EditLinkViewHelper extends AbstractViewHelper {

	/**
	 * @var \CommerceTeam\Commerce\Utility\BackendUtility
	 * @inject
	 */
	protected $commerceBeUtility;

	/**
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $record Entity you generate Edit-Link for
	 * @param string $returnUrl returnUrl
	 * @param string $class class
	 * @return string
	 */
	public function render(AbstractEntity $record, $returnUrl = NULL, $class = '') {
	    $uri = $this->commerceBeUtility->getTcaEditRecordUrl($record, $returnUrl);
		return
			'<a'
				. ' href="' . $uri . '"'
				. ($class?(' class="'.$class.'"'):'')
				. '>' . $this->renderChildren() . '</a>'
			;
	}

}
