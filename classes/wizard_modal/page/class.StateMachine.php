<?php

namespace CourseWizard\Modal\Page;

class StateMachine
{
    const INTRODUCTION_PAGE = "introduction";
    const TEMPLATE_SELECTION_PAGE = "template_selection";
    const CONTENT_INHERITANCE_PAGE = 'content_inheritance';
    const SPECIFIC_SETTINGS_PAGE = "specific_settings";
    const POSTPONE_PAGE = "";

    /** @var ilCtrl */
    protected $ctrl;

    /** @var string */
    protected $current_page;

    /** @var string */
    protected $next_page;

    /** @var string */
    protected $previous_page;

    public function __construct(string $current_page, \ilCtrl $ctrl)
    {
        $this->ctrl = $ctrl;

        $this->current_page = $current_page;

        switch($this->current_page)
        {
            case self::INTRODUCTION_PAGE:
                $this->setPreviousAndNextPage('', self::TEMPLATE_SELECTION_PAGE);
                break;

            case self::TEMPLATE_SELECTION_PAGE:
                $this->setPreviousAndNextPage(self::INTRODUCTION_PAGE, self::CONTENT_INHERITANCE_PAGE);
                break;

            case self::CONTENT_INHERITANCE_PAGE:
                $this->setPreviousAndNextPage(self::TEMPLATE_SELECTION_PAGE, self::SPECIFIC_SETTINGS_PAGE);
                break;

            case self::SPECIFIC_SETTINGS_PAGE:
                $this->setPreviousAndNextPage(self::CONTENT_INHERITANCE_PAGE, '');
                break;

            default:
                throw new InvalidArgumentException('Invalid state for course wizard modal given');
        }
    }

    protected function setPreviousAndNextPage(string $previous, string $next)
    {
        $this->previous_page = $previous;
        $this->next_page = $next;
    }

    public function getPageForPreviousState() : string
    {
        return $this->previous_page;
    }

    public function getPageForCurrentState() : string
    {
        return $this->current_page;
    }

    public function getPageForNextState() : string
    {
        return $this->next_page;
    }
}
