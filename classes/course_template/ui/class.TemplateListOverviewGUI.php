<?php

namespace CourseWizard\CourseTemplate\ui;

use CourseWizard\DB\Models\CourseTemplate;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Item\Standard;
use ILIAS\UI\Component\Link\Link;

abstract class TemplateListOverviewGUI
{
    /** @var string */
    protected $title;

    /** @var array */
    protected $crs_list;

    /** @var \ilCourseWizardPlugin */
    protected $plugin;

    /** @var \ILIAS\UI\Factory */
    protected $ui_factory;

    /** @var \ilCtrl */
    protected $ctrl;

    public function __construct(string $title, array $crs_list, \ilCourseWizardPlugin $plugin, \ILIAS\UI\Factory $ui_factory, \ilCtrl $ctrl)
    {
        $this->title = $title;
        $this->crs_list = $crs_list;
        $this->plugin = $plugin;
        $this->ui_factory = $ui_factory;
        $this->ctrl = $ctrl;
    }

    protected function getEmptyItem() : array
    {
        return array(
            $this->ui_factory->item()->standard($this->plugin->txt('overview_no_crs_templates'))
        );
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    protected function createShowButton(CourseTemplate $course_template) : Link
    {
        return $this->ui_factory->link()->standard($this->plugin->txt('view_course_template'), \ilLink::_getLink($course_template->getCrsRefId()))->withOpenInNewViewport(true);
    }

    public function buildOverviewAsGroupView() : \ILIAS\UI\Component\Item\Group
    {
        $courses = array();

        /** @var \CourseWizard\DB\Models\CourseTemplate $crs_template */
        foreach ($this->crs_list as $crs_template) {
            $title = \ilObject::_lookupTitle($crs_template->getCrsObjId());
            $description = \ilObject::_lookupDescription($crs_template->getCrsObjId());
            $link = \ilLink::_getLink($crs_template->getCrsRefId(), 'crs');
            $title_as_link = $this->ui_factory->link()->standard($title, $link);

            $image_path = $this->plugin->getDirectory() . '/templates/images/icon_crstemp.svg';
            $icon = $this->ui_factory->symbol()->icon()->custom($image_path, 'Thumbnail', 'large');
            $item = $this->ui_factory->item()->standard($title_as_link)
                      ->withDescription($description)
                      ->withProperties([
                          $this->plugin->txt('author') => \ilObjUser::_lookupFullname($crs_template->getCreatorUserId()),
                          $this->plugin->txt('status') => $this->plugin->txt($crs_template->getStatusAsLanguageVariable())
                      ])
                      ->withLeadIcon($icon);

            $actions_buttons = $this->getCommandButtons($crs_template);

            //$actions = $this->prepareActionsDropdownForOwner($crs_template, $f);
            if (count($actions_buttons) > 0) {
                $item = $item->withActions($this->ui_factory->dropdown()->standard($actions_buttons));
            }

            $courses[] = $item;
        }

        $category_title = $this->plugin->txt($this->getTitle());
        $group_items = count($courses) > 0 ? $courses : $this->getEmptyItem();
        return $this->ui_factory->item()->group($category_title, $group_items);
    }

    protected abstract function getCommandButtons(CourseTemplate $course_template) : array;
}