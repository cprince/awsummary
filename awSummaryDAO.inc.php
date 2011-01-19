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
	 * Get visits history
	 * @return array
	 */
	function getVisitsHistory() {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		$result =& $this->retrieve(
			'SELECT * FROM awstats_summary WHERE journal_id=? AND section=? and value1=? ORDER BY year, month',
			array($jid, 'General', 'TotalVisits')
		);
		$data = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$dk = sprintf('%d%02d', $row['year'], $row['month']);
			$dk = date('M Y', strtotime($dk . '01'));
			$data[$dk] = $row['value2'];
			$result->MoveNext();
		}
		$result->Close();

		// make sure array is always 12 elements
		$datalen = count($data);
		if ($datalen > 12) {
			$datakeys = array_keys($data);
			array_splice($data, 0, $datalen-12);
			array_splice($datakeys, 0, $datalen-12);
			$data = array_reverse($data);
			$datakeys = array_reverse($datakeys);

			foreach ($data as $value)
				$ret[array_pop($datakeys)] = array_pop($data);
		}
		if ($datalen < 12) {
			$limit = 12 - $datalen;
			for ($i = 0; $i<$limit; $i++)
				$ret["nd$i"] = 0;
			foreach ($data as $key => $value)
				$ret[$key] = $value;
		}
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
