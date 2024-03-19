<?php

/**
 * Dashboard ILIAS plugin for the assistance system developed as part of the VerDatAs project
 * Copyright (C) 2022-2024 TU Dresden (Tommy Kubica)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class ilVerDatAsDshConfigGUI
 *
 * @author tud <tommy.kubica@tu-dresden.de>
 * @ilCtrl_IsCalledBy ilVerDatAsDshConfigGUI: ilObjComponentSettingsGUI
 */
class ilVerDatAsDshConfigGUI extends ilPluginConfigGUI
{
    private \ILIAS\DI\Container $dic;
    protected ilVerDatAsDshPlugin $pl;

    /**
     * The constructor of ilVerDatAsDshConfigGUI that defines the container variable and retrieves the instance of the ilVerDatAsDshPlugin.
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilVerDatAsDshPlugin::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function performCommand(string $cmd): void
    {
        $this->{$cmd}();
    }

    /**
     * @inheritDoc
     */
    protected function configure(ilPropertyFormGUI $form = null): void
    {
        if (!count(ilCmiXapiLrsTypeList::getTypesData(false))) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('info', $this->pl->txt('missing_lrs_type'), true);
            return;
        }

        if ($form === null) {
            $form = $this->buildForm();
        }

        $this->dic->ui()->mainTemplate()->setContent($form->getHTML());
    }

    /**
     * @inheritDoc
     */
    protected function save()
    {
        $form = $this->buildForm();

        if (!$form->checkInput()) {
            return $this->configure($form);
        }

        $this->writeBackendURL($form->getInput('backend_url'));
        $this->writeLrsTypeId($form->getInput('lrs_type_id'));
        $this->writeUseApi($form->getInput('use_api'));
        $this->writeHideFromStudents($form->getInput('hide_from_students'));
        $this->writeRetrieveCourseMembers($form->getInput('retrieve_course_members'));

        $this->dic->ctrl()->redirect($this, 'configure');
    }

    /**
     * Build the form for configuring the plugin.
     *
     * @return ilPropertyFormGUI
     */
    protected function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));
        $form->addCommandButton('save', $this->dic->language()->txt('save'));
        $form->setTitle($this->pl->txt('configuration'));

        // Backend URL
        $backendURLItem = new ilTextInputGUI($this->pl->txt('backend_url'), 'backend_url');
        $backendURLItem->setRequired(true);

        $backendURLItem->setValue($this->readBackendURL());

        $form->addItem($backendURLItem);

        // LRS type
        $lrsTypeItem = new ilRadioGroupInputGUI($this->pl->txt('lrs_type'), 'lrs_type_id');
        $lrsTypeItem->setRequired(true);

        $types = ilCmiXapiLrsTypeList::getTypesData(false);

        foreach ($types as $type) {
            $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
            $lrsTypeItem->addOption($option);
        }

        $lrsTypeItem->setValue($this->readLrsTypeId());

        $form->addItem($lrsTypeItem);

        // Use API
        $useApiItem = new ilRadioGroupInputGUI($this->pl->txt('use_api'), 'use_api');
        $useApiItem->setRequired(true);

        $optionTrue = new ilRadioOption($this->pl->txt('true'), 1, '');
        $optionFalse = new ilRadioOption($this->pl->txt('false'), 0, '');
        $useApiItem->addOption($optionTrue);
        $useApiItem->addOption($optionFalse);

        $useApiItem->setValue($this->readUseApi());

        $form->addItem($useApiItem);

        // Hide from students
        $hideFromStudentsItem = new ilRadioGroupInputGUI($this->pl->txt('hide_from_students'), 'hide_from_students');
        $hideFromStudentsItem->setRequired(true);

        $hideOptionTrue = new ilRadioOption($this->pl->txt('true'), 1, '');
        $hideOptionFalse = new ilRadioOption($this->pl->txt('false'), 0, '');
        $hideFromStudentsItem->addOption($hideOptionTrue);
        $hideFromStudentsItem->addOption($hideOptionFalse);

        $hideFromStudentsItem->setValue($this->readHideFromStudents());

        $form->addItem($hideFromStudentsItem);

        // Retrieve course members
        $retrieveCourseMembersItem = new ilRadioGroupInputGUI($this->pl->txt('retrieve_course_members'), 'retrieve_course_members');
        $retrieveCourseMembersItem->setRequired(true);

        $retrieveOptionTrue = new ilRadioOption($this->pl->txt('true'), 1, '');
        $retrieveOptionFalse = new ilRadioOption($this->pl->txt('false'), 0, '');
        $retrieveCourseMembersItem->addOption($retrieveOptionTrue);
        $retrieveCourseMembersItem->addOption($retrieveOptionFalse);

        $retrieveCourseMembersItem->setValue($this->readRetrieveCourseMembers());

        $form->addItem($retrieveCourseMembersItem);

        return $form;
    }

    /**
     * Read the defined backend URL from the settings.
     *
     * @return string
     */
    protected function readBackendURL(): string
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        return $settings->get('backend_url');
    }

    /**
     * Write the defined backend URL into the settings.
     *
     * @var string $backendURL
     */
    protected function writeBackendURL(string $backendURL): void
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        $settings->set('backend_url', $backendURL);
    }

    /**
     * Read the selected LRS type from the settings.
     *
     * @return int
     */
    protected function readLrsTypeId(): int
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        return $settings->get('lrs_type_id', 0);
    }

    /**
     * Write the selected LRS type into the settings.
     *
     * @var int $lrsTypeId
     */
    protected function writeLrsTypeId(int $lrsTypeId): void
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        $settings->set('lrs_type_id', $lrsTypeId);
    }

    /**
     * Read the selection whether the API should be used from the settings.
     *
     * @return int
     */
    protected function readUseApi(): int
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        return $settings->get('use_api', 0);
    }

    /**
     * Write the selection whether the API should be used into the settings.
     *
     * @var int $useApi
     */
    protected function writeUseApi(int $useApi): void
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        $settings->set('use_api', $useApi);
    }

    /**
     * Read the selection whether the plugin should be hidden from students from the settings.
     *
     * @return int
     */
    protected function readHideFromStudents(): int
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        return $settings->get('hide_from_students', 0);
    }

    /**
     * Write the selection whether the plugin should be hidden from students into the settings.
     *
     * @var int $hideFromStudents
     */
    protected function writeHideFromStudents(int $hideFromStudents): void
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        $settings->set('hide_from_students', $hideFromStudents);
    }

    /**
     * Read the selection whether the course members should be retrieved from the settings.
     *
     * @return int
     */
    protected function readRetrieveCourseMembers(): int
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        return $settings->get('retrieve_course_members', 0);
    }

    /**
     * Write the selection whether the course members should be retrieved into the settings.
     *
     * @var int $retrieveCourseMembers
     */
    protected function writeRetrieveCourseMembers(int $retrieveCourseMembers): void
    {
        $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
        $settings->set('retrieve_course_members', $retrieveCourseMembers);
    }
}
