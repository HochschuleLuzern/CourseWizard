<?php

namespace CourseWizard\DB\Models;

class UserPreferences
{
    private $user_id;
    private $skip_introductions_clicked;
    private $skip_introductions_clicked_date;

    public function __construct(int $user_id, bool $skip_introductions_clicked, $skip_introductions_clicked_date)
    {
        $this->user_id = $user_id;
        $this->skip_introductions_clicked = $skip_introductions_clicked;
        $this->skip_introductions_clicked_date = $skip_introductions_clicked_date;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function wasSkipIntroductionsClicked() : bool
    {
        return $this->skip_introductions_clicked;
    }

    public function getSkipIntroductionsClickedDate()
    {
        return $this->skip_introductions_clicked_date;
    }

    public function withSkipIntroChanged(bool $skip_intro) : UserPreferences
    {
        $clone = clone $this;
        $clone->skip_introductions_clicked = $skip_intro;
        $clone->skip_introductions_clicked_date = time();
        return $clone;
    }
}