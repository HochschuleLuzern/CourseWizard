<?php declare(strict_types = 1);

class ilCourseWizardJavaScript
{
    public const JS_FUNC_PUSH_TEMPLATE_SELECTION = 'pushTemplateSelection';
    public const JS_FUNC_INTRODUCTION_PAGE_FINISHED = 'introductionPageFinished';
    public const JS_FUNC_LOAD_PREVIOUS_PAGE = 'loadPreviousPage';
    public const JS_FUNC_EXECUTE_IMPORT = 'executeImport';
    public const JS_FUNC_INIT_NEW_MODAL_PAGE = 'initNewModalPage';
    public const JS_FUNC_ADD_INFO_MESSAGE_TO_PAGE = 'addInfoMessageToPage';

    private const PLUGIN_DIRECTORY_LOCATION = 'Customizing/global/plugins/Services/Repository/RepositoryObject/CourseWizard/';

    private const WIZARD_JS_FILE_NAME = 'xcwi_functions.js';
    private const JS_FILES_LOCATION = self::PLUGIN_DIRECTORY_LOCATION .'js/';

    private const WIZARD_CSS_FILE_NAME = 'xcwi_modal_styles.css';
    private const CSS_FILES_LOCATION = self::PLUGIN_DIRECTORY_LOCATION . 'templates/default/' ;

    private static bool $js_files_added_to_template = false;
    private static bool $css_files_added_to_template = false;


    public static function addJsAndCssFileToGlobalTemplate()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();

        if(!self::$js_files_added_to_template) {
            $tpl->addJavaScript(self::JS_FILES_LOCATION. self::WIZARD_JS_FILE_NAME);
            $tpl->addJavaScript(self::JS_FILES_LOCATION . 'jquery.livesearch.js');
            $tpl->addJavaScript(self::JS_FILES_LOCATION . 'quicksilver.js');
            self::$js_files_added_to_template = true;
        }

        if(!self::$css_files_added_to_template) {
            $tpl->addCss(self::CSS_FILES_LOCATION . self::WIZARD_CSS_FILE_NAME);
            self::$css_files_added_to_template = true;
        }
    }


}