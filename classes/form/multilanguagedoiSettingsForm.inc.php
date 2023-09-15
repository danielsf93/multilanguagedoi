<?php

/**
 * @file plugins/importexport/multilanguagedoi/classes/form/multilanguagedoiSettingsForm.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class multilanguagedoiSettingsForm
 * @ingroup plugins_importexport_multilanguagedoi
 *
 * @brief Form for journal managers to setup multilanguagedoi plugin
 */
import('lib.pkp.classes.form.Form');

class multilanguagedoiSettingsForm extends Form
{
    //
    // Private properties
    //
    /** @var int */
    public $_contextId;

    /**
     * Get the context ID.
     *
     * @return int
     */
    public function _getContextId()
    {
        return $this->_contextId;
    }

    /** @var multilanguagedoiExportPlugin */
    public $_plugin;

    /**
     * Get the plugin.
     *
     * @return multilanguagedoiExportPlugin
     */
    public function _getPlugin()
    {
        return $this->_plugin;
    }

    //
    // Constructor
    //

    /**
     * Constructor.
     *
     * @param $plugin multilanguagedoiExportPlugin
     * @param $contextId integer
     */
    public function __construct($plugin, $contextId)
    {
        $this->_contextId = $contextId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

        // DOI plugin settings action link
        $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
        if (isset($pubIdPlugins['doipubidplugin'])) {
            $application = Application::get();
            $request = $application->getRequest();
            $dispatcher = $application->getDispatcher();
            import('lib.pkp.classes.linkAction.request.AjaxModal');
            $doiPluginSettingsLinkAction = new LinkAction(
                'settings',
                new AjaxModal(
                    $dispatcher->url($request, ROUTE_COMPONENT, null, 'grid.settings.plugins.SettingsPluginGridHandler', 'manage', null, ['plugin' => 'doipubidplugin', 'category' => 'pubIds']),
                    __('plugins.importexport.common.settings.DOIPluginSettings')
                ),
                __('plugins.importexport.common.settings.DOIPluginSettings'),
                null
            );
            $this->setData('doiPluginSettingsLinkAction', $doiPluginSettingsLinkAction);
        }

        // Add form validation checks.
        $this->addCheck(new FormValidator($this, 'depositorName', 'required', 'plugins.importexport.multilanguagedoi.settings.form.depositorNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'depositorEmail', 'required', 'plugins.importexport.multilanguagedoi.settings.form.depositorEmailRequired'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    //
    // Implement template methods from Form
    //

    /**
     * @copydoc Form::initData()
     */
    public function initData()
    {
        $contextId = $this->_getContextId();
        $plugin = $this->_getPlugin();
        foreach ($this->getFormFields() as $fieldName => $fieldType) {
            $this->setData($fieldName, $plugin->getSetting($contextId, $fieldName));
        }
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(array_keys($this->getFormFields()));
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $plugin = $this->_getPlugin();
        $contextId = $this->_getContextId();
        foreach ($this->getFormFields() as $fieldName => $fieldType) {
            $plugin->updateSetting($contextId, $fieldName, $this->getData($fieldName), $fieldType);
        }
        parent::execute(...$functionArgs);
    }

    //
    // Public helper methods
    //

    /**
     * Get form fields.
     *
     * @return array (field name => field type)
     */
    public function getFormFields()
    {
        return [
            'depositorName' => 'string',
            'depositorEmail' => 'string',
            'username' => 'string',
            'password' => 'string',
            'automaticRegistration' => 'bool',
            'testMode' => 'bool',
        ];
    }

    /**
     * Is the form field optional.
     *
     * @param $settingName string
     *
     * @return bool
     */
    public function isOptional($settingName)
    {
        return in_array($settingName, ['username', 'password', 'automaticRegistration', 'testMode']);
    }
}
