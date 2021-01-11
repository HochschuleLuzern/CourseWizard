<?php

namespace CourseWizard\Modal\Page;

class JavaScriptPageConfig
{
    public const JS_CURRENT_PAGE = 'currentPage';
    public const JS_NEXT_PAGE = 'nextPage';

    /** @var StateMachine */
    private $state_machine;

    /** @var array */
    private $config_fields;

    public function __construct(StateMachine $state_machine)
    {
        $this->state_machine = $state_machine;
        $this->config_fields = array(
            self::JS_CURRENT_PAGE => $state_machine->getPageForCurrentState(),
            self::JS_NEXT_PAGE => $state_machine->getPageForNextState()
        );
    }

    public function addCustomConfigElement(string $key, string $value)
    {
        $this->config_fields[$key] = $value;
    }

    public function setSaveConfigsURL(string $url)
    {
        $this->config_fields['saveConfigUrl'] = $url;
    }

    public function getAsJSONString() : string
    {
        $json = "{";
        $comma_prefix = "";
        foreach($this->config_fields as $key => $value) {
            $json .= "$comma_prefix $key: \"$value\"";
            $comma_prefix = ',';
        }
        $json .= "}";

        return $json;
    }
}