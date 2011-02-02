<?php

/**
 * @file awSummaryHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class awSummaryHandler
 *
 * @brief awSummary statistics summary.
 */

import('handler.Handler');

class awSummaryHandler extends Handler {
	/** Plugin associated with this request **/
	var $plugin;

	var $domains;
	var $metrics = array(
		'TotalVisits' => 'Total Visits',
		'TotalUnique' => 'Total Unique Visitors',
		);
	var $originLabels = array(
		'From0' => 'Bookmark or Direct',
		'From1' => 'Unknown',
		'From2' => 'Search Engines',
		'From3' => 'Incoming Links',
		);

	/**
	 * Constructor
	 **/
	function awSummaryHandler() {
		parent::Handler();
		$this->domains = $this->_assignDomains();
	}
	
	/**
	 * Display the summary page
	 */
	function index() {
		$this->validate();
		$this->setupTemplate();
		$plugin =& $this->plugin;

		$fullpath = Request::getBaseUrl().'/'.$this->plugin->getPluginPath();

		$awSummaryDao =& DAORegistry::getDAO('awSummaryDAO');
		$templateManager =& TemplateManager::getManager();
		$templateManager->append('stylesheets', "$fullpath/awsummary.css");
		$templateManager->assign('fullpath', $fullpath);


		// determine and format dates
		$year = Request::getUserVar('year');
		$month = Request::getUserVar('month');

		if (!($year && $month)) {
			$sourceperiod = strtotime("last month");
		} else {
			if ($month < 1) $month = 1; if ($month > 12) $month = 12;
			if ($year < 1000) $year = 1000; if ($year > 3000) $year = 3000;

			$sourceperiod = mktime(0,0,0,$month,1,$year);
		}

		$year = date('Y', $sourceperiod);
		$month = date('n', $sourceperiod);

		$datedisplay = date("F Y", $sourceperiod);

		$templateManager->assign('datedisplay', $datedisplay);

		// format the visits
		$visitsHistory = $awSummaryDao->getVisitsHistory();
		foreach ($visitsHistory as $k => $v) {
			unset($visitsHistory[$k]);
			$visitsHistory[str_replace(' ','\n',$k)] = $v;
		}

		$daysmonth = $awSummaryDao->getSectionValues('Days of the Month', $year, $month);
		$totalpages = array_sum($daysmonth);
		$sections = $awSummaryDao->getSections($year, $month);
		$general = $awSummaryDao->getSectionValues('General', $year, $month);
		$incomingsearch = $awSummaryDao->getSectionValues('Search Engines', $year, $month);
		$totalincomingsearch = array_sum($incomingsearch);
		$topincoming = $awSummaryDao->getSectionValues('Page Refs', $year, $month, 10);
		$toppages = $awSummaryDao->getSectionValues('Pages', $year, $month, 25);
		$toparticles = $awSummaryDao->getSectionValues('Pages', $year, $month, 30);
		$origin = $awSummaryDao->getSectionValues('Origin', $year, $month);
		$dpages = $awSummaryDao->getSectionValues('Domain', $year, $month);
		$cities = $awSummaryDao->getSectionValues('GeoIP Cities', $year, $month, 11);
		$searchwords = $awSummaryDao->getSectionValues('Search Keywords', $year, $month, 10);

		// exclude internal links from origin calculations
		if (@$origin['From4'])
			$totalorigin = array_sum($origin) - $origin['From4'];

		foreach ($dpages as $k => $dp) $dpages[$k] = round($dp/$totalpages*100, 1);
		foreach ($incomingsearch as $k => $se) $incomingsearch[$k] = round($se/$totalincomingsearch*100, 1);
		foreach ($origin as $k => $og) $origin[$k] = round($og/$totalorigin*100, 1);

		foreach ($searchwords as $k => $sw) {
			unset($searchwords[$k]);
			$searchwords[urldecode($k)] = $sw;
		}

		if (array_key_exists('unknown',$cities)) unset($cities['unknown']);
		foreach ($cities as $k => $it) {
			unset($cities[$k]);
			$inter = urldecode($k);
			@list($country, $city, $region) = explode("_", $inter);
			if (is_numeric($region[1]) || is_numeric($region[1])) $region = '';
			else $region = strtoupper($region).', ';
			$full = sprintf("%s, %s%s", ucwords($city), $region, $this->domains[$country]);
			$cities[$full] = $it;
		}

		$toppages = $this->_filterArticles($toppages, TRUE);
		$toparticles = $this->_filterArticles($toparticles, FALSE);
		$toparticlesnames = $this->_articleNames($toparticles);

		$templateManager->assign('visitsHistory', $visitsHistory);
		$templateManager->assign('totalpages', $totalpages);
		$templateManager->assign('sections', $sections);
		$templateManager->assign('origin', $origin);
		$templateManager->assign('dpages', $dpages);
		$templateManager->assign('cities', $cities);
		$templateManager->assign('searchwords', $searchwords);
		$templateManager->assign('general', $general);
		$templateManager->assign('incomingsearch', $incomingsearch);
		$templateManager->assign('topincoming', $topincoming);
		$templateManager->assign('toppages', $toppages);
		$templateManager->assign('toparticles', $toparticles);
		$templateManager->assign('toparticlesnames', $toparticlesnames);
		$templateManager->assign_by_ref('domains', $this->domains);
		$templateManager->assign_by_ref('metrics', $this->metrics);
		$templateManager->assign_by_ref('originLabels', $this->originLabels);

		$templateManager->display($plugin->getTemplatePath() . 'index.tpl');
	}

	/**
	 * Internal method
	 */
	function _articleNames($apages) {
		$ret = array();
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		foreach ($apages as $k => $ap) {
			$re = '|article/\w+/(?P<aid>\d+)|';
			preg_match($re, $k, $matches);

			$article =& $articleDao->getArticle($matches['aid']);
			if ($article)
				$title = $article->getArticleTitle();
			else
				$title = '';
			$ret[$k] = $title;
		}
		return $ret;
	}

	/**
	 * Internal method
	 */
	function _filterArticles($apages, $excludeArticles=FALSE) {
		$ret = array();
		foreach ($apages as $k => $ap) {
			if ((
				strpos($k,'article/viewArticle/')
				|| strpos($k,'article/viewPDF')
				|| strpos($k,'article/viewFile')
				|| strpos($k,'article/download')
				|| strpos($k,'article/view/')
				) !== $excludeArticles) {
					$ret[$k] = $apages[$k];
			}
		}
		return $ret;
	}

	/**
	 * Internal method, domains from AWStats lib/domains.pm
	 */
	function _assignDomains() {	
		$ret = array(
		'localhost' => 'localhost',
		'i0' => 'Local network host',
		'a2' => 'Satellite access host',
		'ac' => 'Ascension Island',
		'ad' => 'Andorra',
		'ae' => 'United Arab Emirates',
		'aero' => 'Aero/Travel domains',
		'af' => 'Afghanistan',
		'ag' => 'Antigua and Barbuda',
		'ai' => 'Anguilla',
		'al' => 'Albania',
		'am' => 'Armenia',
		'an' => 'Netherlands Antilles',
		'ao' => 'Angola',
		'aq' => 'Antarctica',
		'ar' => 'Argentina',
		'arpa' => 'Old style Arpanet',
		'as' => 'American Samoa',
		'at' => 'Austria',
		'au' => 'Australia',
		'aw' => 'Aruba',
		'ax' => 'Aland islands',
		'az' => 'Azerbaidjan',
		'ba' => 'Bosnia-Herzegovina',
		'bb' => 'Barbados',
		'bd' => 'Bangladesh',
		'be' => 'Belgium',
		'bf' => 'Burkina Faso',
		'bg' => 'Bulgaria',
		'bh' => 'Bahrain',
		'bi' => 'Burundi',
		'biz' => 'Biz domains',
		'bj' => 'Benin',
		'bm' => 'Bermuda',
		'bn' => 'Brunei Darussalam',
		'bo' => 'Bolivia',
		'br' => 'Brazil',
		'bs' => 'Bahamas',
		'bt' => 'Bhutan',
		'bv' => 'Bouvet Island',
		'bw' => 'Botswana',
		'by' => 'Belarus',
		'bz' => 'Belize',
		'ca' => 'Canada',
		'cc' => 'Cocos (Keeling) Islands',
		'cd' => 'Congo, Democratic Republic of the',
		'cf' => 'Central African Republic',
		'cg' => 'Congo',
		'ch' => 'Switzerland',
		'ci' => 'Ivory Coast (Cote D\'Ivoire)',
		'ck' => 'Cook Islands',
		'cl' => 'Chile',
		'cm' => 'Cameroon',
		'cn' => 'China',
		'co' => 'Colombia',
		'com' => 'Commercial',
		'coop' => 'Coop domains',
		'cr' => 'Costa Rica',
		'cs' => 'Former Czechoslovakia',
		'cu' => 'Cuba',
		'cv' => 'Cape Verde',
		'cx' => 'Christmas Island',
		'cy' => 'Cyprus',
		'cz' => 'Czech Republic',
		'de' => 'Germany',
		'dj' => 'Djibouti',
		'dk' => 'Denmark',
		'dm' => 'Dominica',
		'do' => 'Dominican Republic',
		'dz' => 'Algeria',
		'ec' => 'Ecuador',
		'edu' => 'USA Educational',
		'ee' => 'Estonia',
		'eg' => 'Egypt',
		'eh' => 'Western Sahara',
		'er' => 'Eritrea',
		'es' => 'Spain',
		'et' => 'Ethiopia',
		'eu' => 'European country',
		'fi' => 'Finland',
		'fj' => 'Fiji',
		'fk' => 'Falkland Islands',
		'fm' => 'Micronesia',
		'fo' => 'Faroe Islands',
		'fr' => 'France',
		'fx' => 'France (European Territory)',
		'ga' => 'Gabon',
		'gb' => 'Great Britain',
		'gd' => 'Grenada',
		'ge' => 'Georgia',
		'gf' => 'French Guyana',
		'gg' => 'Guernsey',
		'gh' => 'Ghana',
		'gi' => 'Gibraltar',
		'gl' => 'Greenland',
		'gm' => 'Gambia',
		'gn' => 'Guinea',
		'gov' => 'USA Government',
		'gp' => 'Guadeloupe (French)',
		'gq' => 'Equatorial Guinea',
		'gr' => 'Greece',
		'gs' => 'S. Georgia &amp; S. Sandwich Isls.',
		'gt' => 'Guatemala',
		'gu' => 'Guam (USA)',
		'gw' => 'Guinea Bissau',
		'gy' => 'Guyana',
		'hk' => 'Hong Kong',
		'hm' => 'Heard and McDonald Islands',
		'hn' => 'Honduras',
		'hr' => 'Croatia',
		'ht' => 'Haiti',
		'hu' => 'Hungary',
		'id' => 'Indonesia',
		'ie' => 'Ireland',
		'il' => 'Israel',
		'im' => 'Isle of Man',
		'in' => 'India',
		'info' => 'Info domains',
		'int' => 'International',
		'io' => 'British Indian Ocean Territory',
		'iq' => 'Iraq',
		'ir' => 'Iran',
		'is' => 'Iceland',
		'it' => 'Italy',
		'je' => 'Jersey',
		'jm' => 'Jamaica',
		'jo' => 'Jordan',
		'jobs' => 'Jobs domains',
		'jp' => 'Japan',
		'ke' => 'Kenya',
		'kg' => 'Kyrgyzstan',
		'kh' => 'Cambodia',
		'ki' => 'Kiribati',
		'km' => 'Comoros',
		'kn' => 'Saint Kitts &amp; Nevis Anguilla',
		'kp' => 'North Korea',
		'kr' => 'South Korea',
		'kw' => 'Kuwait',
		'ky' => 'Cayman Islands',
		'kz' => 'Kazakhstan',
		'la' => 'Laos',
		'lb' => 'Lebanon',
		'lc' => 'Saint Lucia',
		'li' => 'Liechtenstein',
		'lk' => 'Sri Lanka',
		'lr' => 'Liberia',
		'ls' => 'Lesotho',
		'lt' => 'Lithuania',
		'lu' => 'Luxembourg',
		'lv' => 'Latvia',
		'ly' => 'Libya',
		'ma' => 'Morocco',
		'mc' => 'Monaco',
		'md' => 'Moldova',
		'me' => 'Montenegro',
		'mg' => 'Madagascar',
		'mh' => 'Marshall Islands',
		'mil' => 'USA Military',
		'mk' => 'Macedonia',
		'ml' => 'Mali',
		'mm' => 'Myanmar',
		'mn' => 'Mongolia',
		'mo' => 'Macau',
		'mobi' => 'Mobi domains',
		'mp' => 'Northern Mariana Islands',
		'mq' => 'Martinique (French)',
		'mr' => 'Mauritania',
		'ms' => 'Montserrat',
		'mt' => 'Malta',
		'mu' => 'Mauritius',
		'museum' => 'Museum domains',
		'mv' => 'Maldives',
		'mw' => 'Malawi',
		'mx' => 'Mexico',
		'my' => 'Malaysia',
		'mz' => 'Mozambique',
		'na' => 'Namibia',
		'name' => 'Name domains',
		'nato' => 'NATO',
		'nc' => 'New Caledonia (French)',
		'ne' => 'Niger',
		'net' => 'Network',
		'nf' => 'Norfolk Island',
		'ng' => 'Nigeria',
		'ni' => 'Nicaragua',
		'nl' => 'Netherlands',
		'no' => 'Norway',
		'np' => 'Nepal',
		'nr' => 'Nauru',
		'nt' => 'Neutral Zone',
		'nu' => 'Niue',
		'nz' => 'New Zealand',
		'om' => 'Oman',
		'org' => 'Non-Profit Organizations',
		'pa' => 'Panama',
		'pe' => 'Peru',
		'pf' => 'Polynesia (French)',
		'pg' => 'Papua New Guinea',
		'ph' => 'Philippines',
		'pk' => 'Pakistan',
		'pl' => 'Poland',
		'pm' => 'Saint Pierre and Miquelon',
		'pn' => 'Pitcairn Island',
		'pr' => 'Puerto Rico',
		'pro' => 'Professional domains',
		'ps' => 'Palestinian Territories',
		'pt' => 'Portugal',
		'pw' => 'Palau',
		'py' => 'Paraguay',
		'qa' => 'Qatar',
		're' => 'Reunion (French)',
		'ro' => 'Romania',
		'rs' => 'Republic of Serbia',
		'ru' => 'Russian Federation',
		'rw' => 'Rwanda',
		'sa' => 'Saudi Arabia',
		'sb' => 'Solomon Islands',
		'sc' => 'Seychelles',
		'sd' => 'Sudan',
		'se' => 'Sweden',
		'sg' => 'Singapore',
		'sh' => 'Saint Helena',
		'si' => 'Slovenia',
		'sj' => 'Svalbard and Jan Mayen Islands',
		'sk' => 'Slovak Republic',
		'sl' => 'Sierra Leone',
		'sm' => 'San Marino',
		'sn' => 'Senegal',
		'so' => 'Somalia',
		'sr' => 'Suriname',
		'st' => 'Sao Tome and Principe',
		'su' => 'Former USSR',
		'sv' => 'El Salvador',
		'sy' => 'Syria',
		'sz' => 'Swaziland',
		'tc' => 'Turks and Caicos Islands',
		'td' => 'Chad',
		'tf' => 'French Southern Territories',
		'tg' => 'Togo',
		'th' => 'Thailand',
		'tj' => 'Tadjikistan',
		'tk' => 'Tokelau',
		'tm' => 'Turkmenistan',
		'tn' => 'Tunisia',
		'to' => 'Tonga',
		'tp' => 'East Timor',
		'tr' => 'Turkey',
		'tt' => 'Trinidad and Tobago',
		'tv' => 'Tuvalu',
		'tw' => 'Taiwan',
		'tz' => 'Tanzania',
		'ua' => 'Ukraine',
		'ug' => 'Uganda',
		'uk' => 'United Kingdom',
		'um' => 'USA Minor Outlying Islands',
		'us' => 'United States',
		'uy' => 'Uruguay',
		'uz' => 'Uzbekistan',
		'va' => 'Vatican City State',
		'vc' => 'Saint Vincent &amp; Grenadines',
		've' => 'Venezuela',
		'vg' => 'Virgin Islands (British)',
		'vi' => 'Virgin Islands (USA)',
		'vn' => 'Vietnam',
		'vu' => 'Vanuatu',
		'wf' => 'Wallis and Futuna Islands',
		'ws' => 'Samoa Islands',
		'ye' => 'Yemen',
		'yt' => 'Mayotte',
		'yu' => 'Yugoslavia',
		'za' => 'South Africa',
		'zm' => 'Zambia',
		'zr' => 'Zaire',
		'zw' => 'Zimbabwe',
		'unknown' => 'Unknown');
		return $ret;
	}
	
	/**
	 * Validate that user has atleast journal manager priveleges.
	 * Redirects to the user index page if not properly authenticated.
	 * @param $canRedirect boolean Whether or not to redirect if the user cannot be validated; if not, the script simply terminates.
	 */
	function validate($canRedirect = true) {
		parent::validate();
		$journal =& Request::getJournal();
		if (!(Validation::isJournalManager() || Validation::isSiteAdmin())) {
			if ($canRedirect) Validation::redirectLogin();
			else exit;
		}

		$plugin =& Registry::get('plugin');
		$this->plugin =& $plugin;
		return true;
	}

	/**
	 * Set up common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the heirarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = array(array(Request::url(null, 'user'), 'navigation.user'));

		if ($subclass) $pageHierarchy[] = array(Request::url(null, 'awSummary'), 'plugins.generic.awSummary');

		$templateMgr->assign_by_ref('pageHierarchy', $pageHierarchy);
	}

}

?>
