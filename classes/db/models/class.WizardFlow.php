<?php

namespace CourseWizard\DB\Models;

class WizardFlow
{
    public const STATUS_IN_PROGRESS = 1; // First status till wizard is either postponed, quited or importing
    public const STATUS_POSTPONED = 2;   // When modal is closed (if the user clicks the "Close"-Button, the "X"-Button or somewhere near the modal)
    public const STATUS_IMPORTING = 3;   // Status while the wizard is importing the content from a course template
    public const STATUS_QUIT = 4;        // Status if user chooses to arrange to course by herself / himself (Final state)
    public const STATUS_FINISHED = 5;    // Status if the wizard has finished importing a course template (Final State)

    private $crs_ref_id;
    private $executing_user;
    private $selected_template;
    private $current_status;
    private $first_open_ts;
    private $finished_import_ts;


    private function __construct($crs_ref_id, $executing_user, $current_status, $first_open_ts, $selected_template, $finished_import_ts)
    {
        $this->crs_ref_id         = $crs_ref_id;
        $this->executing_user     = $executing_user;
        $this->current_status     = $current_status;
        $this->first_open_ts      = $first_open_ts;
        $this->selected_template  = $selected_template;
        $this->finished_import_ts = $finished_import_ts;
    }


    public static function newlyCreatedWizardFlow($target_crs_ref_id, $executing_user) {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_IN_PROGRESS,
            time(),
            null,
            null
        );
    }

    public static function unfinishedWizardFlow($target_crs_ref_id, $executing_user, $first_open_ts, $current_status) {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            $current_status,
            $first_open_ts,
            null,
            null
        );
    }

    public static function finishedWizardFlow($target_crs_ref_id, $executing_user, $first_open_ts, $selected_template, $finished_import_ts) {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_FINISHED,
            $first_open_ts,
            $selected_template,
            $finished_import_ts
        );
    }

    public static function quitedWizardFlow($target_crs_ref_id, $executing_user, $first_open_ts, $finished_import_ts) {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_QUIT,
            $first_open_ts,
            null,
            $finished_import_ts
        );
    }

    public function withPostponedStatus()
    {
        if($this->current_status == self::STATUS_IN_PROGRESS) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_POSTPONED;
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to postponed Status');
        }
    }

    public function withInProgressStatus()
    {
        if($this->current_status == self::STATUS_POSTPONED) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_IN_PROGRESS;
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to postponed Status');
        }
    }

    public function withQuitedStatus()
    {
        if($this->current_status == self::STATUS_IN_PROGRESS || $this->current_status == self::STATUS_QUIT) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_QUIT;
            $clone->finished_import_ts = time();
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to quited Status');
        }
    }

    public function withNewStatus($new_status)
    {
        $clone = clone $this;
        $clone->current_status = $new_status;
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
        return $this->selected_template;
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

    public static function wizardFlowWithSelectedTemplate($crs_ref_id, $template_selection) {
        return new self(
            $crs_ref_id,
            $template_selection,
            null,
            null,
            self::STATUS_IN_PROGRESS
        );
    }

    public static function wizardFlowWithContentInheritance($crs_ref_id, $template_selection, $content_inheritance) {
        return new self(
            $crs_ref_id,
            $template_selection,
            $content_inheritance,
            null,
            self::STATUS_IN_PROGRESS
        );
    }

    public static function wizardFlowFinished($crs_ref_id, $template_selection, $content_inheritance, $selected_settings) {
        return new self(
            $crs_ref_id,
            $template_selection,
            $content_inheritance,
            $selected_settings,
            self::STATUS_FINISHED
        );
    }

}