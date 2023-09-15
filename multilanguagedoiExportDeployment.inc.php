<?php
/**
 * @defgroup plugins_importexport_multilanguagedoi multilanguagedoi export plugin
 */

/*
 * @file plugins/importexport/multilanguagedoi/multilanguagedoiExportDeployment.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class multilanguagedoiExportDeployment
 * @ingroup plugins_importexport_multilanguagedoi
 *
 * @brief Base class configuring the multilanguagedoi export process to an
 * application's specifics.
 */

// XML attributes
define('multilanguagedoi_XMLNS', 'http://www.crossref.org/schema/4.3.6');
define('multilanguagedoi_XMLNS_XSI', 'http://www.w3.org/2001/XMLSchema-instance');
define('multilanguagedoi_XSI_SCHEMAVERSION', '4.3.6');
define('multilanguagedoi_XSI_SCHEMALOCATION', 'https://www.crossref.org/schemas/crossref4.3.6.xsd');
define('multilanguagedoi_XMLNS_JATS', 'http://www.ncbi.nlm.nih.gov/JATS1');
define('multilanguagedoi_XMLNS_AI', 'http://www.crossref.org/AccessIndicators.xsd');

class multilanguagedoiExportDeployment
{
    /** @var Context The current import/export context */
    public $_context;

    /** @var Plugin The current import/export plugin */
    public $_plugin;

    /** @var Issue */
    public $_issue;

    public function getCache()
    {
        return $this->_plugin->getCache();
    }

    /**
     * Constructor.
     *
     * @param $context Context
     * @param $plugin DOIPubIdExportPlugin
     */
    public function __construct($context, $plugin)
    {
        $this->setContext($context);
        $this->setPlugin($plugin);
    }

    //
    // Deployment items for subclasses to override
    //

    /**
     * Get the root lement name.
     *
     * @return string
     */
    public function getRootElementName()
    {
        return 'doi_batch';
    }

    /**
     * Get the namespace URN.
     *
     * @return string
     */
    public function getNamespace()
    {
        return multilanguagedoi_XMLNS;
    }

    /**
     * Get the schema instance URN.
     *
     * @return string
     */
    public function getXmlSchemaInstance()
    {
        return multilanguagedoi_XMLNS_XSI;
    }

    /**
     * Get the schema version.
     *
     * @return string
     */
    public function getXmlSchemaVersion()
    {
        return multilanguagedoi_XSI_SCHEMAVERSION;
    }

    /**
     * Get the schema location URL.
     *
     * @return string
     */
    public function getXmlSchemaLocation()
    {
        return multilanguagedoi_XSI_SCHEMALOCATION;
    }

    /**
     * Get the JATS namespace URN.
     *
     * @return string
     */
    public function getJATSNamespace()
    {
        return multilanguagedoi_XMLNS_JATS;
    }

    /**
     * Get the access indicators namespace URN.
     *
     * @return string
     */
    public function getAINamespace()
    {
        return multilanguagedoi_XMLNS_AI;
    }

    /**
     * Get the schema filename.
     *
     * @return string
     */
    public function getSchemaFilename()
    {
        return $this->getXmlSchemaLocation();
    }

    //
    // Getter/setters
    //

    /**
     * Set the import/export context.
     *
     * @param $context Context
     */
    public function setContext($context)
    {
        $this->_context = $context;
    }

    /**
     * Get the import/export context.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Set the import/export plugin.
     *
     * @param $plugin ImportExportPlugin
     */
    public function setPlugin($plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Get the import/export plugin.
     *
     * @return ImportExportPlugin
     */
    public function getPlugin()
    {
        return $this->_plugin;
    }

    /**
     * Set the import/export issue.
     *
     * @param $issue Issue
     */
    public function setIssue($issue)
    {
        $this->_issue = $issue;
    }

    /**
     * Get the import/export issue.
     *
     * @return Issue
     */
    public function getIssue()
    {
        return $this->_issue;
    }
}
