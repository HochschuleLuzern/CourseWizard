<?php

namespace CourseWizard\CustomUI;

class ViewControlSubpage
{
    protected $subpage_title;

    protected $subpage_content;

    public function __construct(string $subpage_title, $subpage_content)
    {
        $this->subpage_title;
        $this->subpage_content;
    }

    public function getTitle() : string
    {
        return $this->subpage_title;
    }

    public function getContent() : string
    {
        return $this->subpage_content;
    }
}