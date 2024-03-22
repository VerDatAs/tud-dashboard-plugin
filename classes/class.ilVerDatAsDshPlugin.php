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
 * Class ilVerDatAsDshPlugin
 *
 * @author TU Dresden <tommy.kubica@tu-dresden.de>
 */
class ilVerDatAsDshPlugin extends ilPageComponentPlugin
{
    const PLUGIN_ID = "vdsh";
    const PLUGIN_NAME = "VerDatAsDsh";
    protected static ilVerDatAsDshPlugin $instance;

    /**
     * Retrieve the name of the plugin.
     */
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * Ensure that the plugin can only be inserted on course pages and if no other instances of it already exist.
     *
     * @inheritDoc
     */
    public function isValidParentType(string $a_type): bool
    {
        // Early return for content page (copa) and learning module (lm)
        if ($a_type !== 'cont') {
            return false;
        }
        // However, "cont" is used for the course page, the category and the repository
        // Thus, use the ref ID to check for type "crs", else return false
        $refId = $this->getCurrentRefId();
        if (!\ilObject::_exists($refId, true, 'crs')) {
            return false;
        }
        // Check, whether a VerDatAsDsh is already included on the course page
        $tree = new \ilTree(1);
        $courseNode = $tree->getNodeData($refId);
        $containerPage = new \ilContainerPage($courseNode['obj_id']);
        $containerPageXML = $containerPage->getXMLContent();
        $pluginString = '<Plugged PluginName="VerDatAsDsh"';
        $pluginAlreadyIncluded = str_contains($containerPageXML, $pluginString);
        return !$pluginAlreadyIncluded;
    }

    /**
     * Retrieve the instance of the ilVerDatAsDshPlugin.
     *
     * @return ilVerDatAsDshPlugin
     */
    public static function getInstance(): ilVerDatAsDshPlugin
    {
        if (!isset(self::$instance)) {
            global $DIC;
            $componentRepository = $DIC['component.repository'];
            $pluginInfo = $componentRepository->getComponentByTypeAndName('Services', 'COPage')->getPluginSlotById('pgcp')->getPluginByName(self::PLUGIN_NAME);
            $componentFactory = $DIC['component.factory'];
            self::$instance = $componentFactory->getPlugin($pluginInfo->getId());
        }
        return self::$instance;
    }

    /**
     * Retrieve the current ref ID defined in the query parameters of the URL.
     *
     * @return int
     */
    public static function getCurrentRefId(): int
    {
        $refId = filter_input(INPUT_GET, 'ref_id');

        if ($refId === null) {
            $param_target = filter_input(INPUT_GET, 'target');
            $refId = explode('_', $param_target)[1];
        }

        return intval($refId);
    }
}