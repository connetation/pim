<?php

namespace CommerceTeam\Commerce\ViewHelpers\Be;

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
class EditLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper;

	/**
	 * @var \TYPO3\CMS\Backend\Routing\UriBuilder
	 * @inject
	 */
	protected $routingUriBuilder;

	/**
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $record Entity you generate Edit-Link for
	 * @param string $returnUrl returnUrl
	 * @param string $class class
	 * @return string
	 */
	public function render(\TYPO3\CMS\Extbase\DomainObject\AbstractEntity $record, $returnUrl = NULL, $class = '') {
		$table =  $this->dataMapper->getDataMap(get_class($record))->getTableName();

	    $uri = $this->routingUriBuilder->buildUriFromRoute('record_edit', array('edit[' . $table . '][' . $record->getUid() . ']' => 'edit'));

		if (!empty($returnUrl)) {
			$uri .= '&returnUrl=' . rawurlencode($returnUrl);
		}

		return
			'<a'
				. ' href="' . $uri . '"'
				. ($class?(' class="'.$class.'"'):'')
				. '>' . $this->renderChildren() . '</a>'
			;

		$this->tag->addAttribute('href', $uri);
		$this->tag->addAttribute('class', $class);
		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);

		return $this->tag->render();
	}

}
