<?php

/*
 * Thank You to Sebastian Michaelsen
 * https://gist.github.com/smichaelsen/2bfdfb872d04a0776f01
 */


namespace CommerceTeam\Commerce\Utility;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement;

class QueryResult extends \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult {
	/**
	 * Overwrites the original implementation of Extbase
	 *
	 * When the query contains a $statement the query is regularly executed and the number of results is counted
	 * instead of the original implementation which tries to create a custom COUNT(*) query and delivers wrong results.
	 *
	 * @return int The number of matching objects
	 */
	public function count() {
		if ($this->numberOfResults === null) {
			if (is_array($this->queryResult)) {
				$this->numberOfResults = count($this->queryResult);
			} else {
				$statement = $this->query->getStatement();
				if ($statement instanceof Statement) {
					$this->initialize();
					$this->numberOfResults = count($this->queryResult);
				} else {
					return parent::count();
				}
			}
		}
		return $this->numberOfResults;
	}
}
