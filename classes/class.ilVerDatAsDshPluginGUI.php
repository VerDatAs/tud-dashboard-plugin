<?php

/**
 * Dashboard ILIAS plugin for the assistance system developed as part of the VerDatAs project
 * Copyright (C) 2022-2024 TU Dresden (Tommy Kubica, Sebastian Heiden)
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
 * Class ilVerDatAsDshPluginGUI
 *
 * @author tud <tommy.kubica@tu-dresden.de, sebastian.heiden@tu-dresden.de>
 * @ilCtrl_isCalledBy ilVerDatAsDshPluginGUI: ilPCPluggedGUI
 */
class ilVerDatAsDshPluginGUI extends ilPageComponentPluginGUI
{
    const CMD_INSERT = "insert";
    const CMD_CREATE = "create";
    const CMD_SAVE = "save";
    const CMD_EDIT = "edit";
    const CMD_CANCEL = "cancel";
    const MESSAGE_SUCCESS = "msg_obj_modified";
    const DOCUMENTATION_TOOL_NEEDLE = "H5P.DocumentationTool";
    const IL_INTERNAL_LINK_SCRIPT = "goto.php";
    private \ILIAS\DI\Container $dic;
    private ilDBInterface $db;
    protected ilGlobalTemplateInterface $tpl;
    protected ilVerDatAsDshPlugin $pl;

    /**
     * The constructor of ilVerDatAsDshPluginGUI that retrieves several parameters and the instance of the ilVerDatAsDshPlugin.
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->db = $DIC->database();
        $this->tpl = $DIC['tpl'];
        $this->pl = ilVerDatAsDshPlugin::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function executeCommand(): void
    {
        $cmd = $this->dic->ctrl()->getCmd();
        if (in_array($cmd, array(self::CMD_INSERT,
                                 self::CMD_CREATE,
                                 self::CMD_SAVE,
                                 self::CMD_EDIT,
                                 self::CMD_CANCEL
        ))) {
            $this->$cmd();
        }
    }

    /**
     * Display a preview of the dashboard with the options to create the VerDatAsDsh element or cancel.
     *
     * @inheritDoc
     */
    public function insert(): void
    {
        $form = $this->getForm();
        $form->addCommandButton(self::CMD_CREATE, $this->dic->language()->txt(self::CMD_SAVE));
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this, 'create'));

        $customHtml = "<div class='ilFormHeader'>" .
            "<h2 class='ilHeader'>" . $this->pl->txt('include_dashboard_heading') . "</h2>" .
            "<div class='ilHeaderDesc'>" .
                "<p class='alert alert-info' style='margin-top: 20px;'>" . $this->pl->txt('include_dashboard_hint') . "</p>" .
                "<h3 style='margin-bottom: 10px;'>" . $this->pl->txt('preview_heading') . ":</h3>" .
                "<div style='background: #fff; margin-bottom: 10px;'><img src='" . $this->getPlugin()->getDirectory() . "/templates/preview.jpg" . "' width='800' style='max-width: 100%;' alt='" . $this->pl->txt('preview_image_alt') . "'></div>" .
            "</div>" .
        "</div>";

        $this->tpl->setContent($customHtml . $form->getHTML());
    }

    /**
     * Process the create form, i.e., insert the VerDatAsDsh element and return to the parent component (i.e., the page editor) afterward.
     *
     * @inheritDoc
     */
    public function create(): void
    {
        $form = $this->getForm();
        $form->setValuesByPost();

        $properties = [];
        if ($this->createElement($properties)) {
            $this->tpl->setOnScreenMessage('success', $this->dic->language()->txt(self::MESSAGE_SUCCESS), true);
            $this->returnToParent();
        }
    }

    /**
     * Editing the dashboard is not supported. Thus, display an according hint and a cancel button.
     *
     * @inheritDoc
     */
    public function edit(): void
    {
        $form = $this->getForm();
        $form->addCommandButton(self::CMD_CANCEL, $this->dic->language()->txt(self::CMD_CANCEL));
        $form->setFormAction($this->dic->ctrl()->getFormAction($this, 'edit'));

        $customHtml = "<div class='ilFormHeader'>" .
            "<h2 class='ilHeader'>" . $this->pl->txt('edit_dashboard_heading') . "</h2>" .
            "<div class='ilHeaderDesc'>" .
                "<p class='alert alert-info' style='margin-top: 20px;'>" . $this->pl->txt('edit_dashboard_hint') . "</p>" .
                "<h3 style='margin-bottom: 10px;'>" . $this->pl->txt('preview_heading') . ":</h3>" .
                "<div style='background: #fff; margin-bottom: 10px;'><img src='" . $this->getPlugin()->getDirectory() . "/templates/preview.jpg" . "' width='800' style='max-width: 100%;' alt='" . $this->pl->txt('preview_image_alt') . "'></div>" .
            "</div>" .
        "</div>";

        $this->tpl->setContent($customHtml . $form->getHTML());
    }

    /**
     * In case of canceling the action, return to the parent component (i.e., the page editor).
     *
     * @inheritDoc
     */
    function cancel(): void
    {
        $this->returnToParent();
    }

    /**
     * Retrieve the knowledge graph and initialize the dashboard with it.
     *
     * @inheritDoc
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $a_plugin_version): string
    {
        // Check, whether the element is displayed in the edit or presentation mode
        if (in_array($a_mode, ['edit', 'presentation'])) {
            $courseId = \ilVerDatAsDshPlugin::getCurrentRefId();
            // Check, whether the current user should be able to modify the knowledge graph or just view it
            $hasReadAccess = $this->dic->access()->checkAccessOfUser(
                $this->dic->user()->getId(),
                'read', 'read',
                (int) $courseId
            );
            $hasWriteAccess = $this->dic->access()->checkAccessOfUser(
                $this->dic->user()->getId(),
                'write', 'write',
                (int) $courseId
            );
            // Return, if the user has neither read nor write access
            if (!$hasReadAccess && !$hasWriteAccess) {
                return '';
            }
            // Check, whether the user is a student (read only) or a lecturer (both read and write)
            $canViewOnly = $hasReadAccess && !$hasWriteAccess;

            // BEGIN: Retrieve token (similar to the chatbot plugin)
            // Retrieve the settings of the plugin
            $settings = new ilSetting(ilVerDatAsDshPlugin::PLUGIN_ID);
            $lrsTypeId = $settings->get('lrs_type_id', 0);
            $backendURL = $settings->get('backend_url', 0);
            $useAPI = $settings->get('use_api', 0) == 1;
            $hideFromStudents = $settings->get('hide_from_students', 1) == 1;
            $retrieveCourseMembers = $settings->get('retrieve_course_members', 0) == 1;
            // Check, whether the API should be used
            if ($useAPI) {
                // Check, whether the API is installed
                $componentRepository = $this->dic['component.repository'];
                // Reset the value either if the plugin ID of the API does not exist or the plugin is not active
                if (!$componentRepository->hasPluginId('xapi') || !$componentRepository->getPluginById('xapi')->isActive()) {
                    $useAPI = false;
                }
            }
            if (!$lrsTypeId || !$backendURL) {
                return '';
            }
            // Retrieve the selected LRS type
            $lrsType = new ilCmiXapiLrsType($lrsTypeId);

            // Retrieve the name mode defined within the LRS type
            $nameMode = isset(array_flip(get_class_methods($lrsType))['getPrivacyName']) ? $lrsType->getPrivacyIdent() : $lrsType->getUserIdent();
            // Retrieve the user ident for this name mode
            $userIdent = ilCmiXapiUser::getIdent($nameMode, $this->dic->user());

            // Check, whether an expireDate has been set and, if so, whether it has been exceeded
            if (!empty($_SESSION['expireDate'])) {
                if ($_SERVER['REQUEST_TIME'] > $_SESSION['expireDate']) {
                    $_SESSION['userIdent'] = null;
                    $_SESSION['jwt'] = null;
                    $_SESSION['expireDate'] = null;
                }
            }

            // Check, whether at least one session variable is not set
            // Note: As the session terminates on logout, it is not required to check the userIdent for a new logged-in user
            if (empty($_SESSION['userIdent']) || empty($_SESSION['jwt'])) {
                // Prevent a crash, if the VerDatAs-Backend cannot be reached
                try {
                    // Make a request to the VerDatAs-Backend to retrieve the user token, as we need the user ID
                    $verDatAsBackendRequest = new ilVerDatAsDshHttpRequest(
                        $backendURL
                    );
                    $responseBody = $verDatAsBackendRequest->sendPost('/api/v1/auth/login', ['actorAccountName' => $userIdent]);

                    // Decode JWT
                    // https://www.converticacommerce.com/support-maintenance/security/php-one-liner-decode-jwt-json-web-tokens/
                    $arr_body = json_decode($responseBody);
                    $token = $arr_body->token;
                    $parsedToken = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
                    $ident = $parsedToken->sub;
                    $expireDate = $parsedToken->exp;

                    // Define session variables
                    $_SESSION['jwt'] = $token;
                    $_SESSION['userIdent'] = $ident;
                    $_SESSION['expireDate'] = $expireDate;
                } catch (Exception $e) {
                    file_put_contents('console.log', "An error occurred \n", FILE_APPEND);
                }
            } else {
                // Reuse the existing token
                $token = $_SESSION['jwt'];
            }

            // Do not show the dashboard, when the VerDatAs-Backend cannot be accessed
            if (!($token ?? false)) {
                return '';
            }
            // END: Retrieve token

            // Hold variables to retrieve the course structure
            $lcoObject = [];
            $lcoModules = [];
            $lcoTests = [];

            // Hold a variable for the course member IDs (currently necessary to start group collaborations)
            $courseMembers = [];

            // For now, the API does only support retrieving ILIAS learning modules
            // TODO: Extend and define settings for the credentials of the API user
            if ($useAPI) {
                $apiFile = "./Customizing/global/plugins/Services/EventHandling/EventHook/Api/classes/IliasAPIControl/IliasAPIControl.php";
                require_once($apiFile);
                $this->api = new \IliasAPIControl\IliasAPIControl('verdatas');
                try {
                    $this->api->login(
                        'root',
                        'root'
                    );

                    // Extend or adjust object by necessary attributes / values
                    $courseNode = $this->api->course->getData($courseId);
                    $lcoObject['lcoType'] = 'ILIAS_COURSE';
                    $lcoObject['objectId'] = $this->getAttributeArray('objectId', $this->api->course->getLink($courseId));
                    $lcoObject['attributes'] = [
                        $this->getAttributeArray('title', $courseNode['title']),
                        $this->getAttributeArray('description', $courseNode['description'])
                    ];
                    // Currently supported module types of the knowledge graph (by vAPI)
                    // TODO: Add other types as well (or use functionality without vAPI to retrieve the information)
                    $moduleTypesArray = ['lm'];

                    // Sort sub nodes by their titles
                    // "->" is used to call a method, or access a property, on the object of a class
                    // "['prop']" is used to access a key of an array
                    $subNodes = $this->api->course->getSubObjects($courseId);
                    usort($subNodes, function($a, $b) {
                        return strcmp($a['title'], $b['title']);
                    });
                    foreach ($subNodes as $subNode) {
                        // Check, if the type is within the list of supported module types
                        // If it is not in the list, continue
                        if (!in_array($subNode['type'], $moduleTypesArray)) {
                            continue;
                        }
                        $lcoModule = [
                            'lcoType' => 'ILIAS_MODULE'
                        ];
                        $lcoChapters = [];

                        // Learning Module ILIAS
                        if ($subNode['type'] === 'lm') {
                            $learningModule = $this->api->iliasLearningModule->getModuleContent($subNode['ref_id']);
                            $lcoModule['objectId'] = $this->getAttributeArray('objectId', $learningModule['link']);
                            $lcoModule['attributes'] = [
                                $this->getAttributeArray('title', $subNode['title']),
                                $this->getAttributeArray('description', $subNode['description']),
                                $this->getAttributeArray('offline', $subNode['offline'] == '1')
                            ];
                            $lmTreeContent = $learningModule['children'];

                            // TODO: In the following, a similar function could be used for both vAPI and not vAPI. Write a method that is able to support both.
                        }
                        $lcoModule['attributes'][] = $this->getAttributeArray('chapters', $lcoChapters);
                        $lcoModules[] = $lcoModule;
                    }
                } catch (Exception $e) {
                    file_put_contents('console.log', $e->getMessage() . "\n", FILE_APPEND);
                }
            }
            // If the API should not be used, retrieve the structure manually
            else {
                include_once('./Services/Tree/classes/class.ilTree.php');
                require_once('./Modules/LearningModule/classes/class.ilObjLearningModule.php');
                // Retrieve the main tree of the course
                $tree = new \ilTree(1);
                // type: "crs", ref_id -> use for loading knowledge graph
                $courseNode = $tree->getNodeData($courseId);
                // Get the participants of the course, if it is allowed to read them from the course
                if ($hasWriteAccess && $retrieveCourseMembers) {
                    $participants = \ilParticipants::getInstance($courseId);
                    $members = $participants->getMembers();
                    foreach ($members as $member) {
                        $user = new \ilObjUser($member);
                        $courseMembers[] = [
                            'id' => ilCmiXapiUser::getIdent($nameMode, $user),
                            'username' => $user->getLogin()
                        ];
                    }
                }
                // Build the generic format
                $lcoObject['lcoType'] = 'ILIAS_COURSE';
                $lcoObject['objectId'] = $this->getObjectPermaLink('crs', $courseId, $courseNode['obj_id']);
                $lcoObject['attributes'] = [
                    $this->getAttributeArray('title', $courseNode['title']),
                    $this->getAttributeArray('description', $courseNode['description'])
                ];
                // Currently supported module types of the knowledge graph
                $moduleTypesArray = ['lm', 'sahs', 'cmix', 'tst'];
                // Sort sub nodes by their titles
                // "->" is used to call a method, or access a property, on the object of a class
                // "['prop']" is used to access a key of an array
                $subNodes = $tree->getSubTree($courseNode);
                usort($subNodes, function($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                foreach ($subNodes as $subNode) {
                    // Check, if the type is within the list of supported module types
                    // If it is not in the list, continue
                    if (!in_array($subNode['type'], $moduleTypesArray)) {
                        continue;
                    }
                    $lcoModule = [
                        'lcoType' => 'ILIAS_MODULE'
                    ];
                    $lcoChapters = [];

                    // Learning Module ILIAS
                    if ($subNode['type'] === 'lm') {
                        try {
                            // Use code from Sebastian Heiden's extension of the REST plugin
                            // https://github.com/spyfly/Ilias.RESTPlugin/blob/feature/sr-app-routes/RESTController/extensions/learning_module_v1/models/ILIASAppModel.php#L107
                            $learningModule = new \ilObjLearningModule($subNode['ref_id']);
                            $learningModuleObject = \ilObjectFactory::getInstanceByRefId($subNode['ref_id']);
                            $lcoModule['objectId'] = $this->getObjectPermaLink('lm', $subNode['ref_id'], $learningModule->getId());
                            $lcoModule['attributes'] = [
                                $this->getAttributeArray('title', $subNode['title']),
                                $this->getAttributeArray('description', $subNode['description']),
                                $this->getAttributeArray('offline', $subNode['offline'] == '1')
                            ];
                            // Retrieve the structure of the learning module
                            $lmTree = $learningModule->getTree();
                            $lmTreeContent = $lmTree->getSubTree($lmTree->getNodeData($lmTree->readRootId()));

                            // Note: As the tree contains both pages and (sub) chapters, we want to store each of them
                            // Define helper variables
                            $lmChapters = [];
                            $lmSubChapters = [];
                            $lmPages = [];

                            // Note: foreach is faster than using multiple array_filter functions
                            foreach ($lmTreeContent as $lmObj) {
                                if ($lmObj['type'] == 'pg') {
                                    $lmPages[] = $lmObj;
                                }
                                else if ($lmObj['type'] == 'st') {
                                    if ($lmObj['parent'] == '1') {
                                        $lmChapters[] = $lmObj;
                                    } else {
                                        $lmSubChapters[] = $lmObj;
                                    }
                                }
                            }

                            // Iterate chapters and hold all pages related to it
                            foreach ($lmChapters as $lmChapter) {
                                $lcoChapter = [
                                    'lcoType' => 'ILIAS_CHAPTER',
                                    'objectId' => $this->getObjectPermaLink('st', $subNode['ref_id'], $lmChapter['obj_id']),
                                    'attributes' => [
                                        $this->getAttributeArray('title', $lmChapter['title'])
                                    ]
                                ];
                                $lcoContentPages = [];
                                // Filter pages by their direct parent
                                $chapterPages = array_filter($lmPages, function ($pg) use ($lmChapter) { return $pg['parent'] == $lmChapter['obj_id']; });
                                // Find sub-chapters by their direct parent
                                $subChaptersOfChapter = array_filter($lmSubChapters, function ($st) use ($lmChapter) { return $st['parent'] == $lmChapter['obj_id']; });
                                // If sub-chapters exist, find pages having those sub-chapters as parent and add them to the list of chapter pages
                                if (count($subChaptersOfChapter) > 0) {
                                    foreach ($subChaptersOfChapter as $subChapter) {
                                        $subChapterPages = array_filter($lmPages, function ($pg) use ($subChapter) { return $pg['parent'] == $subChapter['obj_id']; });
                                        $chapterPages = array_merge($chapterPages, $subChapterPages);
                                    }
                                }
                                // Iterate pages of the chapter
                                foreach ($chapterPages as $chapterPage) {
                                    $lmPage = new \ilLMPage($chapterPage['obj_id']);
                                    $pageObjectId = $this->getObjectPermaLink('pg', $subNode['ref_id'], $lmPage->getId());
                                    $pageDetails = $this->getPageDetails($lmPage, $subNode['ref_id'], $pageObjectId);
                                    $lcoContentPage = [
                                        'lcoType' => 'ILIAS_CONTENT_PAGE',
                                        'objectId' => $pageObjectId,
                                        'attributes' => [
                                            $this->getAttributeArray('title', $chapterPage['title']),
                                            $this->getAttributeArray('content', $pageDetails['xmlContent'])
                                        ]
                                    ];
                                    // Iterate questions and documentation tools of the page
                                    $lcoInteractiveTasks = [];
                                    $lcoDocumentationTools = [];
                                    foreach ($pageDetails['interactiveTasks'] as $pageQuestion) {
                                        $lcoInteractiveTask = [
                                            'lcoType' => 'ILIAS_INTERACTIVE_TASK',
                                            'objectId' => $pageQuestion['object_id'],
                                            'attributes' => [
                                                $this->getAttributeArray('title', $pageQuestion['title'])
                                            ]
                                        ];
                                        $lcoInteractiveTasks[] = $lcoInteractiveTask;
                                    }
                                    foreach ($pageDetails['documentationTools'] as $pageDocumentationTool) {
                                        $lcoDocumentationTool = [
                                            'lcoType' => 'ILIAS_DOCUMENTATION_TOOL',
                                            'objectId' => $pageDocumentationTool['object_id'],
                                            'attributes' => [
                                                $this->getAttributeArray('title', $pageDocumentationTool['title'])
                                            ]
                                        ];
                                        $lcoDocumentationTools[] = $lcoDocumentationTool;
                                    }

                                    $lcoContentPage['attributes'][] = $this->getAttributeArray('interactiveTasks', $lcoInteractiveTasks);
                                    $lcoContentPage['attributes'][] = $this->getAttributeArray('documentationTools', $lcoDocumentationTools);
                                    $lcoContentPages[] = $lcoContentPage;
                                }

                                $lcoChapter['attributes'][] = $this->getAttributeArray('contentPages', $lcoContentPages);
                                $lcoChapters[] = $lcoChapter;
                            }
                        } catch (Exception $e) {
                            file_put_contents('console.log', $e->getMessage() . "\n", FILE_APPEND);
                        }
                    }
                    // Learning Module SCORM
                    else if ($subNode['type'] === 'sahs') {
                        try {
                            $sahsModule = new \ilObjSAHSLearningModule($subNode['ref_id']);
                            $subType = $sahsModule->getSubType();
                            // Note: Currently, only SCORM2004 modules are supported
                            if ($subType === 'scorm2004') {
                                include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
                                $scormModule = new \ilObjSCORM2004LearningModule($subNode['ref_id']);
                                // Hint: $scormModule cannot be printed in the console, but for the $scormTree it is possible.
                                $scormTree = $scormModule->getTree();
                                // TODO: For the moment, we did not manage to receive this data from the object itself. Thus, a database access is used.
                                // Further tables of interest: cp_node, cp_dependency
                                $query = 'SELECT * FROM cp_package WHERE obj_id=' . $scormTree->tree_id;
                                $res = $this->db->query($query);
                                if ($row = $this->db->fetchAssoc($res)) {
                                    $scormData = json_decode($row['jsdata'], true);
                                    if ($scormData['item'] ?? false) {
                                        // SCORM module attributes
                                        $scormItem = $scormData['item'];
                                        // example: "il_0_sahs_28116"
                                        $lcoModule['objectId'] = $scormItem['id'];
                                        $lcoModule['attributes'] = [
                                            $this->getAttributeArray('title', $scormItem['title']),
                                            $this->getAttributeArray('description', $subNode['description']),
                                            $this->getAttributeArray('offline', $subNode['offline'] == '1')
                                        ];
                                        // Iterate SCORM chapters
                                        foreach ($scormItem['item'] as $scormChapter) {
                                            // SCORM chapter attributes
                                            // Do only consider IDs similar to "il_0_chap_297", but no assets ("il_0_ass_444")
                                            if (str_contains($scormChapter['id'], 'chap')) {
                                                $lcoChapter = [
                                                    'lcoType' => 'ILIAS_CHAPTER',
                                                    'objectId' => $scormChapter['id'],
                                                    'attributes' => [
                                                        $this->getAttributeArray('title', $scormChapter['title'])
                                                    ]
                                                ];
                                                $lcoContentPages = [];
                                                // SCORM page attributes
                                                // "il_0_sco_35"
                                                // Iterate SCORM chapter pages
                                                foreach ($scormChapter['item'] as $scormPage) {
                                                    $lcoContentPage = [
                                                        'lcoType' => 'ILIAS_CONTENT_PAGE',
                                                        'objectId' => $scormPage['id'],
                                                        'attributes' => [
                                                            $this->getAttributeArray('title', $scormPage['title'])
                                                        ]
                                                    ];
                                                    // TODO: Retrieve the content and interactive tasks as well
                                                    $lcoContentPages[] = $lcoContentPage;
                                                }
                                                $lcoChapter['attributes'][] = $this->getAttributeArray('contentPages', $lcoContentPages);
                                                $lcoChapters[] = $lcoChapter;
                                            }
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            file_put_contents('console.log', $e->getMessage() . "\n", FILE_APPEND);
                        }
                    }
                    // xAPI/cmi5 (Typo3)
                    else if ($subNode['type'] === 'cmix') {
                        try {
                            require_once('./Modules/CmiXapi/classes/class.ilObjCmiXapi.php');
                            $cmixObject = new \ilObjCmiXapi($subNode['ref_id']);
                            $launchUrl = $cmixObject->getLaunchUrl();
                            // The following methods might be interesting as well:
                            // $launchParameters = $cmixObject->getLaunchParameters();
                            // $launchMethod = $cmixObject->getLaunchMethod(); // e.g., newWin
                            // $launchMode = $cmixObject->getLaunchMode(); // e.g., Normal
                            // $privacyIdent = $cmixObject->getPrivacyIdent(); // e.g., 5
                            // $xmlManifest = $cmixObject->getXmlManifest(); // full xml

                            // Note: The requests could also be bundled for multiple cmix modules
                            // Get typo3 server URL to send the request to
                            $startExplodeIndex = (str_contains($launchUrl, 'http://') || str_contains($launchUrl, 'https://')) ? 3 : 1;
                            $slugInputExplode = explode('/', $launchUrl);
                            $startSlug = '/' . $slugInputExplode[$startExplodeIndex];
                            $startSlugIndex = strpos($launchUrl, $startSlug);
                            $typo3ServerUrl = substr($launchUrl, 0, $startSlugIndex);

                            // Make a request to the Typo3 REST API to retrieve the structure of the module
                            $typo3Request = new ilVerDatAsDshHttpRequest(
                                $typo3ServerUrl
                            );
                            $typo3ResponseBody = $typo3Request->sendPost('/api/module/structure', [['slug' => $launchUrl]]);

                            // Decode response body
                            $decodedBody = json_decode($typo3ResponseBody);
                            // For the moment, only consider having one input slug
                            if (count($decodedBody) == 1) {
                                // Retrieve first element
                                $learningModule = array_pop(array_reverse($decodedBody));
                                $lcoModule['objectId'] = $typo3ServerUrl . $learningModule->slug;
                                $lcoModule['attributes'] = [
                                    $this->getAttributeArray('title', $subNode['title']),
                                    $this->getAttributeArray('description', $subNode['description']),
                                    $this->getAttributeArray('offline', $subNode['offline'] == '1')
                                ];

                                // Iterate chapters
                                foreach ($learningModule->chapters as $chapter) {
                                    $lcoChapter = [
                                        'lcoType' => 'ILIAS_CHAPTER',
                                        'objectId' => $typo3ServerUrl . $chapter->slug,
                                        'attributes' => [
                                            $this->getAttributeArray('title', $chapter->title)
                                        ]
                                    ];
                                    $lcoContentPages = [];
                                    foreach ($chapter->pages as $contentPage) {
                                        $lcoContentPage = [
                                            'lcoType' => 'ILIAS_CONTENT_PAGE',
                                            'objectId' => $typo3ServerUrl . $contentPage->slug,
                                            'attributes' => [
                                                $this->getAttributeArray('title', $contentPage->title)
                                            ]
                                        ];
                                        $lcoInteractiveTasks = [];
                                        $lcoDocumentationTools = [];
                                        // Iterate content to retrieve the interactive tasks
                                        foreach ($contentPage->content as $content) {
                                            if (!$content->tx_h5p_content) {
                                                continue;
                                            }
                                            $h5pContent = array_pop(array_reverse($content->tx_h5p_content));
                                            if (str_contains($h5pContent->library, self::DOCUMENTATION_TOOL_NEEDLE)) {
                                                $lcoDocumentationTool = [
                                                    'lcoType' => 'ILIAS_DOCUMENTATION_TOOL',
                                                    'objectId' => $typo3ServerUrl . $h5pContent->iframeSlug,
                                                    'attributes' => [
                                                        $this->getAttributeArray('title', $h5pContent->title),
                                                        $this->getAttributeArray('subType', $h5pContent->library)
                                                    ]
                                                ];
                                                $lcoDocumentationTools[] = $lcoDocumentationTool;
                                            } else {
                                                $lcoInteractiveTask = [
                                                    'lcoType' => 'ILIAS_INTERACTIVE_TASK',
                                                    'objectId' => $typo3ServerUrl . $h5pContent->iframeSlug,
                                                    'attributes' => [
                                                        $this->getAttributeArray('title', $h5pContent->title),
                                                        $this->getAttributeArray('subType', $h5pContent->library)
                                                    ]
                                                ];
                                                $lcoInteractiveTasks[] = $lcoInteractiveTask;
                                            }
                                        }
                                        $lcoContentPage['attributes'][] = $this->getAttributeArray('interactiveTasks', $lcoInteractiveTasks);
                                        $lcoContentPage['attributes'][] = $this->getAttributeArray('documentationTools', $lcoDocumentationTools);
                                        $lcoContentPages[] = $lcoContentPage;
                                    }
                                    $lcoChapter['attributes'][] = $this->getAttributeArray('contentPages', $lcoContentPages);
                                    $lcoChapters[] = $lcoChapter;
                                }
                            }
                        } catch (Exception $e) {
                            file_put_contents('console.log', "An error occurred \n", FILE_APPEND);
                        }
                    }
                    else if ($subNode['type'] === 'tst') {
                        $lcoTest = [
                            'lcoType' => 'ILIAS_TEST',
                            'objectId' => $this->getObjectPermaLink('tst', $subNode['ref_id'], $courseId),
                            'attributes' => [
                                $this->getAttributeArray('title', $subNode['title']),
                                $this->getAttributeArray('description', $subNode['description']),
                                $this->getAttributeArray('offline', $subNode['offline'] == '1'),
                            ]
                        ];
                        $lcoTests[] = $lcoTest;
                    }
                    // Do not push tests into the modules
                    if ($subNode['type'] !== 'tst') {
                        $lcoModule['attributes'][] = $this->getAttributeArray('chapters', $lcoChapters);
                        $lcoModules[] = $lcoModule;
                    }
                }
            }

            $lcoObject['attributes'][] = $this->getAttributeArray('modules', $lcoModules);
            $lcoObject['attributes'][] = $this->getAttributeArray('tests', $lcoTests);

            if (!$canViewOnly || !$hideFromStudents) {
                // Add custom template, which will load the JavaScript file
                $tpl = $this->getPlugin()->getTemplate('tpl.content.html');

                // Add templatePath variable to specify the current path
                $templatePath = $this->getPlugin()->getDirectory() . '/templates';
                $tpl->setVariable('TEMPLATE_PATH', $templatePath);

                // Define the dashboard data that is processed by the application
                $initDashboardData = '{
                  "path": ' . json_encode($templatePath) .',
                  "courseNode": ' . json_encode($lcoObject) .',
                  "token": ' . json_encode($token) .',
                  "backendUrl": ' . json_encode($backendURL) .',
                  "canViewOnly": ' . json_encode($canViewOnly) .',
                  "previewMode": ' . json_encode($a_mode === "edit") .',
                  "members": ' . json_encode($courseMembers) .',
                  "pseudoId": ' . json_encode($userIdent) .'
                }';

                // Initialize the VerDatAsDashboard on load of the main template, which works for most cases (a workaround for the other cases exists, too)
                $this->dic->ui()->mainTemplate()->addOnLoadCode('VerDatAsDashboard.init(' . $initDashboardData . ')');
                return $tpl->get();
            }
        }
        return '';
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getForm(): ilPropertyFormGUI
    {
        return new ilPropertyFormGUI();
    }

    /**
     * Retrieve the details of an ILIAS page (including the interactive tasks and documentation tools).
     *
     * Code retrieved from Sebastian Heiden:
     * https://github.com/spyfly/Ilias.RESTPlugin/blob/af267b4cbb71d452889b5159fda9acd7c04b1e86/RESTController/extensions/learning_module_v1/models/ILIASAppModel.php#L142
     *
     * @param ilLMPage $pageObj
     * @param int      $learningModuleRefId
     * @param string   $pageObjectId
     * @return array
     */
    private function getPageDetails(ilLMPage $pageObj, int $learningModuleRefId, string $pageObjectId): array
    {
        $pageObj->buildDom();
        $questionIds = $pageObj->getQuestionIds();
        $pool = new \ilObjQuestionPool();
        $xmlContent = $pageObj->getXMLContent();

        // Retrieve both the interactive tasks and documentation tools of H5P
        preg_match_all('/<Plugged PluginName=\\"H5PPageComponent\\" [^>]*><PluggedProperty Name=\\"content_id\\">([0-9]*)<\/PluggedProperty><\/Plugged>/', $xmlContent, $h5pRaw);
        $questionsXhfp = [];
        $documentationTools = [];
        if (count($h5pRaw[1]) > 0) {
            $db = $this->db;
            foreach ($h5pRaw[1] as $h5pContentId) {
                $hset = $db->query("SELECT name, parameters, rep_robj_xhfp_cont.title, content_id FROM rep_robj_xhfp_lib LEFT JOIN rep_robj_xhfp_cont ON rep_robj_xhfp_lib.library_id = rep_robj_xhfp_cont.library_id WHERE rep_robj_xhfp_cont.content_id = " . $db->quote($h5pContentId,
                        "integer"));
                // Note: The attribute "parameter" gets lost at this position, as the code crashes otherwise
                // $row = $db->fetchAssoc($hset);
                // https://stackoverflow.com/a/29147028/3623608
                $h5pObject = [];
                while ($row = $db->fetchAssoc($hset)) {
                    $h5pObject['name'] = $row['name'];
                    $h5pObject['title'] = $row['title'];
                    $h5pObject['content_id'] = $row['content_id'];
                    $h5pObject['object_id'] = $this->getObjectPermaLink('h5p', $learningModuleRefId, $h5pContentId, $pageObj->getId());
                    $h5pObject['page_object_id'] = $pageObjectId;
                    if (str_contains($row['name'], self::DOCUMENTATION_TOOL_NEEDLE)) {
                        $documentationTools[] = $h5pObject;
                    } else {
                        // Only add H5P task, if it exists
                        $questionsXhfp[] = $h5pObject;
                    }
                }
            }
        }

        // Retrieve the ILIAS questions
        $iliasQuestionsAsXML = count($questionIds) > 0 ? $pool->questionsToXML($questionIds) : "";
        $iliasQuestions = [];

        if ($iliasQuestionsAsXML !== "") {
            $xml = simplexml_load_string($iliasQuestionsAsXML);
            foreach($xml->item as $xmlItem)
            {
                $questionObject = [];
                $questionObject['content_id'] = (string)$xmlItem->attributes()->ident;
                // Retrieve question type from metadata
                $fieldLabel = (string)$xmlItem->itemmetadata->qtimetadata->qtimetadatafield[1]->fieldlabel;
                $subType = $fieldLabel == 'QUESTIONTYPE' ? (string)$xmlItem->itemmetadata->qtimetadata->qtimetadatafield[1]->fieldentry : 'Unknown';
                $questionObject['name'] = $subType;
                $questionObject['title'] = (string)$xmlItem->attributes()->title;
                // Retrieve ID as an int from the content_id, e.g., "il_0_qst_3"
                $iliasQuestionId = count(explode("_", $questionObject['content_id'])) > 3 ? explode("_", $questionObject['content_id'])[3] : -1;
                // For the moment, use "ilq" for ILIAS questions
                $questionObject['object_id'] = $this->getObjectPermaLink('ilq', $learningModuleRefId, $iliasQuestionId, $pageObj->getId());
                $questionObject['page_object_id'] = $pageObjectId;
                $iliasQuestions[] = $questionObject;
            }
        }

        return [
            "xmlContent" => $xmlContent,
            "interactiveTasks" => array_merge($iliasQuestions, $questionsXhfp),
            "documentationTools" => $documentationTools
        ];
    }

    /**
     * Retrieve a unique permanent link for different elements.
     *
     * Expected behavior (similar to ILIAS perma link):
     * - Course: http://localhost:8080/goto.php?target=crs_82&client_id=default&obj_id_lrs=311
     * - Learning module: http://localhost:8080/goto.php?target=lm_83&client_id=default&obj_id_lrs=315
     * - Chapter: http://localhost:8080/goto.php?target=st_8_83&client_id=default&obj_id_lrs=315
     * - Page within learning module: http://localhost:8080/goto.php?target=pg_11_83&client_id=default&obj_id_lrs=315
     * - H5P question on page of learning module: http://localhost:8080/goto.php?target=pg_11_83&client_id=default&h5p_object_id=11&obj_id_lrs=315
     *
     * @param string $objectType
     * @param int    $referenceId
     * @param int    $objectId
     * @param int    $pageId
     * @param bool   $withObjId
     * @return string
     */
    public function getObjectPermaLink(string $objectType, int $referenceId, int $objectId, int $pageId = -1, bool $withObjId = true): string
    {
        $stringIds = '';
        $object = \ilObjectFactory::getInstanceByRefId($referenceId);
        $objId = preg_replace('%([\D].*)%', '', $object->getId());

        if ($withObjId) {
            if ($objectType == 'h5p') {
                $stringIds .= '&h5p_object_id=' . $objectId;
            }
            if ($objectType == 'ilq') {
                $stringIds .= '&ilq_object_id=' . $objectId;
            }
            $stringIds .= '&obj_id_lrs=' . $objId;
        }

        switch ($objectType) {
            case 'crs':
            case 'lm':
            case 'tst':
                $reference = $referenceId;
                break;
            case 'st':
            case 'pg':
                $reference = $objectId . '_' . $referenceId;
                break;
            case 'h5p':
            case 'ilq':
                // Note: Dirty workaround for setting the URL to the learning module
                $objectType = 'pg';
                $reference = $pageId . '_' . $referenceId;
                break;
            default:
                $reference = $referenceId;
        }

        // Note: The implementation of ilLink::_getLink was used, as it does only allow adding int as $reference
        return ILIAS_HTTP_PATH . '/' . self::IL_INTERNAL_LINK_SCRIPT . '?target=' . $objectType . '_' . $reference . '&client_id=' . CLIENT_ID . $stringIds;
    }

    /**
     * Helper function to generate the required generic format.
     *
     * @param string $attributeKey
     * @param mixed $attributeValue
     * @return array
     */
    private function getAttributeArray(string $attributeKey, mixed $attributeValue): array
    {
        return [
            'key' => $attributeKey,
            'value' => $attributeValue
        ];
    }
}
