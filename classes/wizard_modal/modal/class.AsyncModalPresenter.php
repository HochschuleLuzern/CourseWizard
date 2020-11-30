<?php

namespace CourseWizard\Modal;

class AsyncModalPresenter
{
    public function __construct(\ILIAS\UI\Factory $ui_factory)
    {
        $this->ui_factory = $ui_factory;
        $this->async_modal = $ui_factory->modal()->roundtrip('', $this->ui_factory->legacy(''));
    }

    public function getModal()
    {
        return $this->async_modal;
    }
}