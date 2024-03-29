<?php

/**
 * @defgroup plugins_importexport_multilanguagedoi multilanguagedoi Plugin
 */

/**
 * @file plugins/importexport/multilanguagedoi/index.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_multilanguagedoi
 * @brief Wrapper for multilanguagedoi export plugin.
 */
require_once 'multilanguagedoiExportPlugin.inc.php';

return new multilanguagedoiExportPlugin();
