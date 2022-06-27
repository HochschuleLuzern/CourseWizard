<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

use ILIAS\UI\Component\Image\Image;

class CourseImportLoadingStepUIComponents
{
    private string $title;
    private string $content;
    private ?Image $status_icon;
    private ?string $rendered_status_icon;

    public function __construct(string $title, string $content, Image $status_icon = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->status_icon = $status_icon;
        $this->rendered_status_icon = null;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function hasStatusIcon() : bool
    {
        return isset($this->status_icon);
    }

    public function getRenderedStatusIcon() : string
    {
        if(is_null($this->rendered_status_icon)) {
            global $DIC;

            if(!is_null($this->status_icon)) {
                $this->rendered_status_icon = $DIC->ui()->renderer()->render($this->status_icon);
            } else {
                return '';
            }
        }
        return $this->rendered_status_icon;
    }

    public static function getLoadingSteps(\ilCourseWizardPlugin $plugin) : array
    {
        global $DIC;

        $loading_icon = $DIC->ui()->factory()->image()->standard('templates/default/images/loader.svg', 'loading');
        return array(
            new CourseImportLoadingStepUIComponents(
                $plugin->txt('import_content_page_loading_title'),
                $plugin->txt('import_content_page_loading_content'),
                $loading_icon),
            new CourseImportLoadingStepUIComponents(
                $plugin->txt('import_objects_loading_title'),
                $plugin->txt('import_object_loading_content'),
                $loading_icon)
        );
    }

    public static function getLoadingStepsWithCopyTable(\ilObjectCopyProgressTableGUI $progress, \ilCourseWizardPlugin $plugin) : array
    {
        global $DIC;

        $success_icon = $DIC->ui()->factory()->image()->standard('templates/default/images/icon_ok.svg', 'success');
        return array(
            new CourseImportLoadingStepUIComponents(
                $plugin->txt('import_content_page_loading_title'),
                $plugin->txt('import_content_page_loading_content'),
                $success_icon),
            new CourseImportLoadingStepUIComponents(
                $plugin->txt('import_objects_loading_title'),
                $progress->getHTML())
        );

    }
}