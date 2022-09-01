<?php

use PHPUnit\Framework\TestCase;

class CourseWizardGeneralTesting extends TestCase
{
    /**
     * @test
     * @small
     */
    public function testDICElementReplacement()
    {
        global $DIC;
        $ui_factory_mock = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory_mock->expects($this->atLeastOnce())->method('legacy')->willReturn('');
        $ui_mock = $this->createMock(\ILIAS\UI\Factory::class);//->expects($this->once())->method('factory')->willReturn($ui_factory_mock);
        $DIC->database();
        $DIC->register($ui_mock);
        $this->assertEquals($ui_mock, $DIC->ui());
    }

    /**
     * @test
     * @small
     */
    public function testPrivateMethods()
    {
        //$this->createMock()
    }
}
