<?php

use CourseWizard\DB\Models\WizardFlow;
use CourseWizard\Modal\ModalDataController;
use CourseWizard\Modal\Page\JavaScriptPageConfig;
use CourseWizard\Modal\Page\StateMachine;
use PHPUnit\Framework\TestCase;

class ModalDataControllerTest extends TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @test
     * @small
     */
    public function testGetCurrentWizardStepFromPostedData_NullGiven_ExceptionExpected()
    {
        // Arrange
        $given = StateMachine::TEMPLATE_SELECTION_PAGE;
        $post_data = array(JavaScriptPageConfig::JS_CURRENT_PAGE => $given);
        $expected = WizardFlow::STEP_TEMPLATE_SELECTION;
        $wizard_flow_repo_mock = $this->createMock(\CourseWizard\DB\WizardFlowRepository::class);
        $modal_data_controller = new ModalDataController($wizard_flow_repo_mock);

        // Act
        $result = $this->invokeMethod($modal_data_controller, 'getCurrentWizardStepFromPostedPage', [$post_data]);

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @small
     */
    public function testGetCurrentWizardStepFromPostedData__ExceptionExpected()
    {
        // Arrange
        $wizard_flow_repo_mock = $this->createMock(\CourseWizard\DB\WizardFlowRepository::class);
        $modal_data_controller = new ModalDataController($wizard_flow_repo_mock);
        $post_data = array();
        $this->expectException(ReflectionException::class);

        // Act
        $this->invokeMethod($modal_data_controller, 'getCurrentWizardStepFromPostedPage', [$post_data]);

        // Assert
    }
}
