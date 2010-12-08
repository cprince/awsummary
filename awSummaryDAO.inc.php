<?php

/**
 * @file plugins/generic/awsummary/awSummaryDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class awSummaryDAO
 *
 * @brief Class for managing awSummary records.
 */


class awSummaryDAO extends DAO {

	/**
	 * Get sections
	 * @return array
	 */
	function getSections($year, $month) {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		$result =& $this->retrieve(
			'SELECT section FROM awstats_summary WHERE journal_id=? AND year=? AND month=? GROUP BY section',
			array($jid, $year, $month)
		);
		$ret = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$ret[] = $row['section'];
			$result->MoveNext();
		}
		$result->Close();
		return $ret;
	}

	/**
	 * Get values for a section
	 * @return array
	 */
	function getSectionValues($section, $year, $month, $limit=-1) {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		if ($limit==-1) {
			$result =& $this->retrieve(
				'SELECT value1, value2 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ORDER BY rank',
				array($jid, $section, $year, $month)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT value1, value2 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ORDER BY rank LIMIT ?',
				array($jid, $section, $year, $month, $limit)
			);
		}
		$ret = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$ret[$row['value1']] = $row['value2'];
			$result->MoveNext();
		}
		$result->Close();
		return $ret;
	}


}

?>
