<?php declare(strict_types = 1);

namespace CourseWizard\DB\Models;

class WizardFlow
{
    public const STATUS_IN_PROGRESS = 1; // First status till wizard is either postponed, quited or importing
    public const STATUS_POSTPONED = 2;   // When modal is closed (if the user clicks the "Close"-Button, the "X"-Button or somewhere near the modal)
    public const STATUS_IMPORTING = 3;   // Status while the wizard is importing the content from a course template
    public const STATUS_QUIT = 4;        // Status if user chooses to arrange to course by herself / himself (Final state)
    public const STATUS_FINISHED = 5;    // Status if the wizard has finished importing a course template (Final State)

    private int $crs_ref_id;
    private int $executing_user;
    private ?int $selected_template;
    private int $current_status;
    private ?\DateTimeInterface $first_open;
    private ?\DateTimeInterface $finished_import;


    private function __construct(int $crs_ref_id, int $executing_user, int $current_status, ?\DateTimeInterface $first_open, ?int $selected_template, ?\DateTimeInterface $finished_import)
    {
        $this->crs_ref_id = $crs_ref_id;
        $this->executing_user = $executing_user;
        $this->current_status = $current_status;
        $this->first_open = $first_open;
        $this->selected_template = $selected_template;
        $this->finished_import = $finished_import;
    }


    public static function newlyCreatedWizardFlow(int $target_crs_ref_id, int $executing_user) : WizardFlow
    {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_IN_PROGRESS,
            new \DateTimeImmutable(),
            null,
            null
        );
    }

    public static function wizardFlowImporting(int $target_crs_ref_id, int $executing_user, ?\DateTimeImmutable $first_open, int $selected_template) : WizardFlow
    {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_IMPORTING,
            $first_open,
            $selected_template,
            null
        );
    }

    public static function unfinishedWizardFlow(int $target_crs_ref_id, int $executing_user, ?\DateTimeImmutable $first_open_ts, int $current_status) : WizardFlow
    {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            $current_status,
            $first_open_ts,
            null,
            null
        );
    }

    public static function finishedWizardFlow(int $target_crs_ref_id, int $executing_user, ?\DateTimeImmutable $first_open_ts, int $selected_template, ?\DateTimeImmutable $finished_import_ts) : WizardFlow
    {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_FINISHED,
            $first_open_ts,
            $selected_template,
            $finished_import_ts
        );
    }

    public static function quitedWizardFlow(int $target_crs_ref_id, int $executing_user, ?\DateTimeImmutable $first_open_ts, ?\DateTimeImmutable $finished_import_ts) : WizardFlow
    {
        return new self(
            $target_crs_ref_id,
            $executing_user,
            self::STATUS_QUIT,
            $first_open_ts,
            null,
            $finished_import_ts
        );
    }

    public function withPostponedStatus() : WizardFlow
    {
        if ($this->current_status == self::STATUS_IN_PROGRESS) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_POSTPONED;
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to postponed Status');
        }
    }

    public function withInProgressStatus() : WizardFlow
    {
        if ($this->current_status == self::STATUS_POSTPONED) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_IN_PROGRESS;
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to postponed Status');
        }
    }

    public function withImportingStatus($selected_template) : WizardFlow
    {
        if($this->current_status == self::STATUS_IN_PROGRESS) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_IMPORTING;
            $clone->selected_template = $selected_template;

            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to postponed Status');
        }
    }

    public function withQuitedStatus() : WizardFlow
    {
        if ($this->current_status == self::STATUS_IN_PROGRESS
            || $this->current_status == self::STATUS_QUIT
            || $this->getCurrentStatus() == self::STATUS_POSTPONED) {

            $clone = clone $this;
            $clone->current_status = self::STATUS_QUIT;
            $clone->finished_import = new \DateTimeImmutable();
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to quited Status');
        }
    }

    public function withFinishedStatus() : WizardFlow
    {
        if ($this->current_status == self::STATUS_IMPORTING) {
            $clone = clone $this;
            $clone->current_status = self::STATUS_FINISHED;
            $clone->finished_import = new \DateTimeImmutable();
            return $clone;
        } else {
            throw new \InvalidArgumentException('Illegal change of Status Code Nr. ' . $this->current_status . ' to quited Status');
        }
    }

    public function withNewStatus(int $new_status) : WizardFlow
    {
        $clone = clone $this;
        $clone->current_status = $new_status;
        return $clone;
    }

    public function getCrsRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function getTemplateSelection() : ?int
    {
        return $this->selected_template;
    }

    public function getCurrentStatus() : int
    {
        return $this->current_status;
    }

    public function getExecutingUser() : int
    {
        return $this->executing_user;
    }

    public function getFirstOpen() : ?\DateTimeInterface
    {
        return $this->first_open;
    }

    public function getFinishedImport() : ?\DateTimeInterface
    {
        return $this->finished_import;
    }
}
