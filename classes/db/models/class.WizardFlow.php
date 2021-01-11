<?php

namespace CourseWizard\DB\Models;

class WizardFlow
{
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_POSTPONED = 2;
    public const STATUS_QUIT = 3;
    public const STATUS_IMPORTING = 4;
    public const STATUS_FINISHED = 5;

    public const STEP_INTRODUCTION = 1;
    public const STEP_TEMPLATE_SELECTION = 2;
    public const STEP_CONTENT_INHERITANCE = 3;
    public const STEP_SELECTED_SETTINGS = 4;
    public const STEP_FINISHED_WIZARD = 5;

    private $crs_ref_id;
    private $template_selection;
    private $content_inheritance;
    private $selected_settings;
    private $current_status;
    private $current_step;

    private function __construct($crs_ref_id, $template_selection, $content_inheritance, $selected_settings, $current_status, $current_step)
    {
        $this->crs_ref_id = $crs_ref_id;
        $this->template_selection = $template_selection;
        $this->content_inheritance = $content_inheritance;
        $this->selected_settings = $selected_settings;
        $this->current_status = $current_status;
        $this->current_step = $current_step;
    }

    public function withSelectedTemplate($selected_template) {
        $clone = clone $this;
        $clone->template_selection = $selected_template;
        $clone->current_step = self::STEP_TEMPLATE_SELECTION;
        return $clone;
    }

    public function withSelectedInheritance($content_inheritance) {
        $clone = clone $this;
        $clone->content_inheritance = $content_inheritance;
        $clone->current_step = self::STEP_CONTENT_INHERITANCE;
        return $clone;
    }

    public function withSelectedSettings($selected_settings) {
        $clone = clone $this;
        $clone->selected_settings = $selected_settings;
        $clone->current_step = self::STEP_SELECTED_SETTINGS;
        return $clone;
    }

    /**
     * @return mixed
     */
    public function getCrsRefId()
    {
        return $this->crs_ref_id;
    }

    /**
     * @return mixed
     */
    public function getTemplateSelection()
    {
        return $this->template_selection;
    }

    /**
     * @return mixed
     */
    public function getContentInheritance()
    {
        return $this->content_inheritance;
    }

    /**
     * @return mixed
     */
    public function getSelectedSettings()
    {
        return $this->selected_settings;
    }

    /**
     * @return mixed
     */
    public function getCurrentStatus()
    {
        return $this->current_status;
    }

    /**
     * @return mixed
     */
    public function getCurrentStep()
    {
        return $this->current_step;
    }

    public static function newWizardFlow($crs_ref_id) {
        return new self(
            $crs_ref_id,
            null,
            null,
            null,
            self::STATUS_IN_PROGRESS,
            self::STEP_INTRODUCTION
        );
    }

    public static function wizardFlowWithSelectedTemplate($crs_ref_id, $template_selection) {
        return new self(
            $crs_ref_id,
            $template_selection,
            null,
            null,
            self::STATUS_IN_PROGRESS,
            self::STEP_TEMPLATE_SELECTION
        );
    }

    public static function wizardFlowWithContentInheritance($crs_ref_id, $template_selection, $content_inheritance) {
        return new self(
            $crs_ref_id,
            $template_selection,
            $content_inheritance,
            null,
            self::STATUS_IN_PROGRESS,
            self::STEP_CONTENT_INHERITANCE
        );
    }

    public static function wizardFlowFinished($crs_ref_id, $template_selection, $content_inheritance, $selected_settings) {
        return new self(
            $crs_ref_id,
            $template_selection,
            $content_inheritance,
            $selected_settings,
            self::STATUS_FINISHED,
            self::STEP_FINISHED_WIZARD
        );
    }
}