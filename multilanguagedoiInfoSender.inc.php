<?php

/**
 * @file plugins/importexport/multilanguagedoi/multilanguagedoiInfoSender.php
 *
 * Copyright (c) 2013-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class multilanguagedoiInfoSender
 * @ingroup plugins_importexport_multilanguagedoi
 *
 * @brief Scheduled task to send deposits to multilanguagedoi and update statuses.
 */
import('lib.pkp.classes.scheduledTask.ScheduledTask');

class multilanguagedoiInfoSender extends ScheduledTask
{
    /** @var multilanguagedoiExportPlugin */
    public $_plugin;

    /**
     * Constructor.
     */
    public function __construct($args)
    {
        PluginRegistry::loadCategory('importexport');
        $plugin = PluginRegistry::getPlugin('importexport', 'multilanguagedoiExportPlugin'); /* @var $plugin multilanguagedoiExportPlugin */
        $this->_plugin = $plugin;

        if (is_a($plugin, 'multilanguagedoiExportPlugin')) {
            $plugin->addLocaleData();
        }

        parent::__construct($args);
    }

    /**
     * @copydoc ScheduledTask::getName()
     */
    public function getName()
    {
        return __('plugins.importexport.multilanguagedoi.senderTask.name');
    }

    /**
     * @copydoc ScheduledTask::executeActions()
     */
    public function executeActions()
    {
        if (!$this->_plugin) {
            return false;
        }

        $plugin = $this->_plugin;
        $journals = $this->_getJournals();

        foreach ($journals as $journal) {
            $notify = false;

            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journal->getId());
            $doiPubIdPlugin = $pubIdPlugins['doipubidplugin'];

            if ($doiPubIdPlugin->getSetting($journal->getId(), 'enablePublicationDoi')) {
                // Get unregistered articles
                $unregisteredArticles = $plugin->getUnregisteredArticles($journal);
                // If there are articles to be deposited
                if (count($unregisteredArticles)) {
                    $this->_registerObjects($unregisteredArticles, 'article=>multilanguagedoi-xml', $journal, 'articles');
                }
            }
        }

        return true;
    }

    /**
     * Get all journals that meet the requirements to have
     * their articles or issues DOIs sent to multilanguagedoi.
     *
     * @return array
     */
    public function _getJournals()
    {
        $plugin = $this->_plugin;
        $contextDao = Application::getContextDAO(); /* @var $contextDao JournalDAO */
        $journalFactory = $contextDao->getAll(true);

        $journals = [];
        while ($journal = $journalFactory->next()) {
            $journalId = $journal->getId();
            if (!$plugin->getSetting($journalId, 'username') || !$plugin->getSetting($journalId, 'password') || !$plugin->getSetting($journalId, 'automaticRegistration')) {
                continue;
            }

            $doiPrefix = null;
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $journalId);
            if (isset($pubIdPlugins['doipubidplugin'])) {
                $doiPubIdPlugin = $pubIdPlugins['doipubidplugin'];
                if (!$doiPubIdPlugin->getSetting($journalId, 'enabled')) {
                    continue;
                }
                $doiPrefix = $doiPubIdPlugin->getSetting($journalId, 'doiPrefix');
            }

            if ($doiPrefix) {
                $journals[] = $journal;
            } else {
                $this->addExecutionLogEntry(__('plugins.importexport.common.senderTask.warning.noDOIprefix', ['path' => $journal->getPath()]), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            }
        }

        return $journals;
    }

    /**
     * Register objects.
     *
     * @param $objects array
     * @param $filter string
     * @param $journal Journal
     * @param $objectsFileNamePart string
     */
    public function _registerObjects($objects, $filter, $journal, $objectsFileNamePart)
    {
        $plugin = $this->_plugin;
        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        // The new multilanguagedoi deposit API expects one request per object.
        // On contrary the export supports bulk/batch object export, thus
        // also the filter expects an array of objects.
        // Thus the foreach loop, but every object will be in an one item array for
        // the export and filter to work.
        foreach ($objects as $object) {
            // export XML
            $exportXml = $plugin->exportXML([$object], $filter, $journal);
            // Write the XML to a file.
            // export file name example: multilanguagedoi-20160723-160036-articles-1-1.xml
            $objectsFileNamePartId = $objectsFileNamePart.'-'.$object->getId();
            $exportFileName = $plugin->getExportFileName($plugin->getExportPath(), $objectsFileNamePartId, $journal, '.xml');
            $fileManager->writeFile($exportFileName, $exportXml);
            // Deposit the XML file.
            $result = $plugin->depositXML($object, $journal, $exportFileName);
            if ($result !== true) {
                $this->_addLogEntry($result);
            }
            // Remove all temporary files.
            $fileManager->deleteByPath($exportFileName);
        }
    }

    /**
     * Add execution log entry.
     *
     * @param $result array
     */
    public function _addLogEntry($result)
    {
        if (is_array($result)) {
            foreach ($result as $error) {
                assert(is_array($error) && count($error) >= 1);
                $this->addExecutionLogEntry(
                    __($error[0], ['param' => (isset($error[1]) ? $error[1] : null)]),
                    SCHEDULED_TASK_MESSAGE_TYPE_WARNING
                );
            }
        }
    }
}
