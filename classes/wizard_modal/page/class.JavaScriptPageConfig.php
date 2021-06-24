<?php

namespace CourseWizard\Modal\Page;

class JavaScriptPageConfig
{
    public const JS_PREVIOUS_PAGE = 'previousPage';
    public const JS_PREVIOUS_PAGE_URL = 'previousPageUrl';
    public const JS_CURRENT_PAGE = 'currentPage';
    public const JS_CURRENT_PAGE_URL = 'currentPageUrl';
    public const JS_NEXT_PAGE = 'nextPage';
    public const JS_NEXT_PAGE_URL = 'nextPageUrl';


    /** @var StateMachine */
    private $state_machine;

    /** @var array */
    private $config_fields;

    public function __construct(StateMachine $state_machine)
    {
        $this->state_machine = $state_machine;
        $this->config_fields = array(
            self::JS_PREVIOUS_PAGE => $state_machine->getPageForPreviousState(),
            self::JS_CURRENT_PAGE => $state_machine->getPageForCurrentState(),
            self::JS_NEXT_PAGE => $state_machine->getPageForNextState()
        );
    }

    public function addCustomConfigElement(string $key, string $value)
    {
        $this->config_fields[$key] = $value;
    }

    public function setPageSwitchURL(string $previousPage, string $currentPage, string $nextPage)
    {
        $this->config_fields[self::JS_PREVIOUS_PAGE_URL] = $previousPage;
        $this->config_fields[self::JS_CURRENT_PAGE_URL] = $currentPage;
        $this->config_fields[self::JS_NEXT_PAGE_URL] = $nextPage;
    }

    public function getAsJSONString() : string
    {
        return json_encode($this->config_fields);
    }
}
