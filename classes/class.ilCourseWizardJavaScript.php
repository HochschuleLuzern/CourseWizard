<?php

class ilCourseWizardJavaScript
{
    public const JS_FUNC_PUSH_TEMPLATE_SELECTION = 'pushTemplateSelection';
    public const JS_FUNC_INTRODUCTION_PAGE_FINISHED = 'introductionPageFinished';
    public const JS_FUNC_PUSH_CONTENT_INHERITANCE_SELECTION = 'pushContentInheritanceSelection';
    public const JS_FUNC_LOAD_PREVIOUS_PAGE = 'loadPreviousPage';
    public const JS_FUNC_EXECUTE_IMPORT = 'executeImport';
    public const JS_FUNC_INIT_NEW_MODAL_PAGE = 'initNewModalPage';
    public const JS_FUNC_ADD_INFO_MESSAGE_TO_PAGE = 'addInfoMessageToPage';

    private const PLUGIN_DIRECTORY_LOCATION = 'Customizing/global/plugins/Services/Repository/RepositoryObject/CourseWizard/';

    private const JS_FILE_NAME = 'xcwi_functions.js';
    private const JS_FILE_LOCATION = self::PLUGIN_DIRECTORY_LOCATION .'js/' . self::JS_FILE_NAME;

    private const CSS_FILE_NAME = 'xcwi_modal_styles.css';
    private const CSS_FILE_LOCATION = self::PLUGIN_DIRECTORY_LOCATION . 'templates/default/' . self::CSS_FILE_NAME;

    private static $js_file_added_to_template = false;
    private static $css_file_added_to_template = false;


    public static function addJsAndCssFileToGlobalTemplate()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();

        if(!self::$js_file_added_to_template) {
            $tpl->addJavaScript(self::JS_FILE_LOCATION);
            self::$js_file_added_to_template = true;
        }

        if(!self::$css_file_added_to_template) {
            $tpl->addCss(self::CSS_FILE_LOCATION);
            self::$css_file_added_to_template = true;
        }
    }


}