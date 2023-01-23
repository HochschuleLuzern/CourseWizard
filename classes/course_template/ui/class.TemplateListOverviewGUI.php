<?php

namespace CourseWizard\CourseTemplate\ui;

use CourseWizard\DB\Models\CourseTemplate;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Item\Standard;
use ILIAS\UI\Component\Link\Link;

abstract class TemplateListOverviewGUI
{
    protected $additional_ui_elements;

    protected $parent_gui_obj;

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

    public function __construct($parent_gui_obj, string $title, array $crs_list, \ilCourseWizardPlugin $plugin, \ILIAS\UI\Factory $ui_factory, \ilCtrl $ctrl)
    {
        $this->parent_gui_obj = $parent_gui_obj;
        $this->title = $title;
        $this->crs_list = $crs_list;
        $this->plugin = $plugin;
        $this->ui_factory = $ui_factory;
        $this->ctrl = $ctrl;
        $this->additional_ui_elements = array();
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
        return $this->ui_factory->link()->standard($this->plugin->txt('view_course_template'), \ilLink::_getLink($course_template->getCrsRefId()))->withOpenInNewViewport(false);
    }

    protected function createDeleteButton(CourseTemplate $course_template)
    {
        $this->ctrl->setParameter($this->parent_gui_obj, \ilObjCourseWizardGUI::GET_DEP_ID, $course_template->getCrsRefId());
        $link = $this->ctrl->getLinkTarget($this->parent_gui_obj, \ilObjCourseWizardGUI::CMD_DELETE_TEMPLATE_MODAL); //\ilLink::_getLink($this->ref_id, $this->getType(), array('dep_id' => $crs_template->getCrsRefId()));
        $this->ctrl->setParameter($this->parent_gui_obj, \ilObjCourseWizardGUI::GET_DEP_ID, '');

        $delete_modal = $this->ui_factory
            ->modal()
            ->interruptive(
                '',
                '',
                ''
            )->withAsyncRenderUrl($link);

        $this->additional_ui_elements[] = $delete_modal;
        return $this->ui_factory->button()->shy($this->plugin->txt('btn_delete_crs_template'), $delete_modal->getShowSignal());
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

    public function getAdditionalUIElements() : array
    {
        return $this->additional_ui_elements;
    }

    abstract protected function getCommandButtons(CourseTemplate $course_template) : array;
}
