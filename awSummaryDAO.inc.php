<?php

/**
 * @file plugins/generic/awsummary/awSummaryDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function getVisitsHistory($maxMonths) {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		$result =& $this->retrieve(
			'SELECT * FROM awstats_summary WHERE journal_id=? AND section=? and value1=? ORDER BY year, month',
			array($jid, 'General', 'TotalVisits')
		);
		$data = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$dk = sprintf('%d%02d01', $row['year'], $row['month']);
			$dk = date('M Y', strtotime($dk));
			$data[$dk] = $row['value2'];
			$result->MoveNext();
		}
		$result->Close();

		// make sure array is always $maxMonths elements
		$datalen = count($data);
		if ($datalen == $maxMonths) {
			$ret = $data;
		}
		if ($datalen > $maxMonths) {
			$datakeys = array_keys($data);
			array_splice($data, 0, $datalen-$maxMonths);
			array_splice($datakeys, 0, $datalen-$maxMonths);
			$data = array_reverse($data);
			$datakeys = array_reverse($datakeys);

			foreach ($data as $value)
				$ret[array_pop($datakeys)] = array_pop($data);
		}
		if ($datalen < $maxMonths) {
			$limit = $maxMonths - $datalen;
			for ($i = 0; $i<$limit; $i++)
				$ret["nd$i"] = 0;
			foreach ($data as $key => $value)
				$ret[$key] = $value;
		}
		return $ret;
	}

	/**
	 * Get values for the city section - for some reason page counts do not get reported for the geoip_city_maxmind awstats plugin
	 * @return array
	 */
	function getCityValues($year, $month, $limit=-1) {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		$result =& $this->retrieve(
			'SELECT value1, value3 FROM awstats_summary
				WHERE journal_id=? AND section=? AND year=? AND month=? ORDER BY CAST(value3 as signed integer) desc',
			array($jid, 'GeoIP Cities', $year, $month)
		);
		$hits = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$hits[$row['value1']] = $row['value3'];
			$result->MoveNext();
		}
		$result->Close();
		$hitstotal = array_sum($hits);

		if ($limit==-1) {
			$result =& $this->retrieve(
				'SELECT value1, value3 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ORDER BY CAST(value3 as signed integer) desc',
				array($jid, 'GeoIP Cities', $year, $month)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT value1, value3 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ORDER BY CAST(value3 as signed integer) desc LIMIT ?',
				array($jid, 'GeoIP Cities', $year, $month, $limit)
			);
		}
		$ret = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$ret[$row['value1']] = round($row['value3']/$hitstotal*100,1);
			$result->MoveNext();
		}
		$result->Close();
		return $ret;
	}

	/**
	 * Get values for a section
	 * @return array
	 */
	function getSectionValues($section, $year, $month, $limit=-1, $custom='') {
		$currentJournal =& Request::getJournal();
		$jid = $currentJournal->getId();

		if ($limit==-1) {
			$result =& $this->retrieve(
				'SELECT value1, value2 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ' . $custom . ' ORDER BY CAST(value2 as signed integer) desc',
				array($jid, $section, $year, $month)
			);
		} else {
			$result =& $this->retrieve(
				'SELECT value1, value2 FROM awstats_summary
					WHERE journal_id=? AND section=? AND year=? AND month=? ' . $custom . ' ORDER BY CAST(value2 as signed integer) desc LIMIT ?',
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
