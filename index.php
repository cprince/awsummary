<?php

/**
 * @file plugins/generic/awsummary/index.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Wrapper for awSummary stats plugin.
 *
 */

// $Id: index.php,v 1.8 2009/04/08 19:54:40 asmecher Exp $


require_once('awSummaryPlugin.inc.php');

return new awSummaryPlugin();

?>
