<?php

/**
 * @file awSummaryPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class awSummaryPlugin
 *
 * @brief awSummary plugin; provides awSummary statistics.
 */


import('classes.plugins.GenericPlugin');

class awSummaryPlugin extends GenericPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$isEnabled = $this->getSetting(0, 'enabled');
		$success = parent::register($category, $path);
		if ($success && $isEnabled === true) {

			HookRegistry::register ('Templates::Admin::Index::AdminFunctions', array(&$this, 'displayMenuOption'));
			HookRegistry::register ('Templates::Manager::Index::ManagementPages', array(&$this, 'displayMenuOption'));
			HookRegistry::register ('LoadHandler', array(&$this, 'handleRequest'));

			$this->import('awSummaryDAO');
			$awSummaryDao = new awSummaryDAO();
			DAORegistry::registerDAO('awSummaryDAO', $awSummaryDao);
		}
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'awSummaryPlugin';
	}

	function getDisplayName() {
		$this->addLocaleData();
		return Locale::translate('plugins.generic.awsummary');
	}

	function getDescription() {
		$this->addLocaleData();
		return Locale::translate('plugins.generic.awsummary.description');
	}

	/**
	 * Get the filename of the ADODB schema for this plugin.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/' . 'schema.xml';
	}

	function displayMenuOption($hookName, $args) {
		if (!Validation::isJournalManager()) return false;

		$params =& $args[0];
		$smarty =& $args[1];
		$output =& $args[2];

		$this->addLocaleData();
		$output .= '<li>&#187; <a href="' . Request::url(null, 'awsummary') . '">' . Locale::translate('plugins.generic.awsummary') . '</a></li>';
		return false;
	}


	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		// If the request is for the log analyzer itself, handle it.
		if ($page === 'awsummary') {
			$this->addLocaleData();
			$this->import('awSummaryHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'awSummaryHandler');
			return true;
		}

		return false;
	}

	function getManagementVerbs() {
		$this->addLocaleData();
		$isEnabled = $this->getSetting(0, 'enabled');

		$verbs = array();

		// Non-site admin managers cannot manage awSummary plugin.
		if (!Validation::isSiteAdmin()) return $verbs;

		if ($isEnabled) {
			$verbs[] = array(
				'awsummary',
				Locale::translate('plugins.generic.awsummary')
			);
			$this->import('awSummaryDAO');
			$awSummaryDao = new awSummaryDAO();
			DAORegistry::registerDAO('awSummaryDAO', $awSummaryDao);
			$awSummaryDao =& DAORegistry::getDAO('awSummaryDAO');
		}
		$verbs[] = array(
			($isEnabled?'disable':'enable'),
			Locale::translate($isEnabled?'manager.plugins.disable':'manager.plugins.enable')
		);
		return $verbs;
	}

 	/*
 	 * Execute a management verb on this plugin
 	 * @param $verb string
 	 * @param $args array
	 * @param $message string Location for the plugin to put a result msg
 	 * @return boolean
 	 */
	function manage($verb, $args, &$message) {
		// Non-site admin managers cannot manage awSummary plugin.
		if (!Validation::isSiteAdmin()) return false;

		$isEnabled = $this->getSetting(0, 'enabled');
		$this->addLocaleData();
		switch ($verb) {
			case 'migrate':
				$awSummaryDao =& DAORegistry::getDAO('awSummaryDAO');
				$awSummaryDao->upgradeFromLogFile();
				Request::redirect('index', 'awsummary');
				break;
			case 'enable':
				$this->updateSetting(0, 'enabled', true);
				$message = Locale::translate('plugins.generic.awsummary.enabled');
				break;
			case 'disable':
				$this->updateSetting(0, 'enabled', false);
				$message = Locale::translate('plugins.generic.awsummary.disabled');
				break;
			case 'awsummary':
				if ($isEnabled) Request::redirect(null, 'awsummary');
		}
		return false;
	}
}

?>
