<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit71f87bf9dc756d3f9fbef51b34a2eae1
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CourseWizard\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CourseWizard\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'ContentInheritanceData' => __DIR__ . '/../..' . '/classes/course_import/ContentInheritanceData.php',
        'CourseImportController' => __DIR__ . '/../..' . '/classes/course_import/CourseImportController.php',
        'CourseImportData' => __DIR__ . '/../..' . '/classes/course_import/CourseImportData.php',
        'CourseImportObjectFactory' => __DIR__ . '/../..' . '/classes/course_import/CourseImportObjectFactory.php',
        'CourseWizard\\CourseTemplate\\CourseTemplateCollector' => __DIR__ . '/../..' . '/classes/course_template/class.CourseTemplateCollector.php',
        'CourseWizard\\CourseTemplate\\CourseTemplateCreator' => __DIR__ . '/../..' . '/classes/course_template/class.CourseTemplateCreator.php',
        'CourseWizard\\CourseTemplate\\CourseTemplateManagementTableDataProvider' => __DIR__ . '/../..' . '/classes/course_template/management/class.CourseTemplateManagementTableDataProvider.php',
        'CourseWizard\\CourseTemplate\\CourseTemplateManagementTableGUI' => __DIR__ . '/../..' . '/classes/course_template/management/class.CourseTemplateManagementTableGUI.php',
        'CourseWizard\\CourseTemplate\\CourseTemplateObject' => __DIR__ . '/../..' . '/classes/course_template/class.CourseTemplateObject.php',
        'CourseWizard\\CustomUI\\ContentInheritanceTableGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/ContentInheritanceTableGUI.php',
        'CourseWizard\\CustomUI\\RadioGroupViewControlSubPageGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/class.RadioGroupViewControlSubPageGUI.php',
        'CourseWizard\\CustomUI\\RadioSelectionViewControlGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/class.RadioSelectionViewControlGUI.php',
        'CourseWizard\\CustomUI\\TemplateSelectionRadioGroupGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/class.TemplateSelectionRadioGroupGUI.php',
        'CourseWizard\\CustomUI\\TemplateSelectionRadioOptionGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/class.TemplateSelectionRadioOptionGUI.php',
        'CourseWizard\\CustomUI\\ViewControlSubpageGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/custom_ui/ViewControlSubpageGUI.php',
        'CourseWizard\\DB\\CourseTemplateRepository' => __DIR__ . '/../..' . '/classes/db/class.CourseTemplateRepository.php',
        'CourseWizard\\DB\\CourseWizardSpecialQueries' => __DIR__ . '/../..' . '/classes/db/class.CourseWizardSpecialQueries.php',
        'CourseWizard\\DB\\Models\\CourseTemplate' => __DIR__ . '/../..' . '/classes/db/models/class.CourseTemplate.php',
        'CourseWizard\\DB\\Models\\CourseTemplateTraits' => __DIR__ . '/../..' . '/classes/db/models/CourseTemplateTraits.php',
        'CourseWizard\\DB\\Models\\TemplateContainerConfiguration' => __DIR__ . '/../..' . '/classes/db/models/class.TemplateContainerConfiguration.php',
        'CourseWizard\\DB\\Models\\WizardFlow' => __DIR__ . '/../..' . '/classes/db/models/class.WizardFlow.php',
        'CourseWizard\\DB\\PluginConfigKeyValueStore' => __DIR__ . '/../..' . '/classes/db/class.PluginConfigKeyValueStore.php',
        'CourseWizard\\DB\\TemplateContainerConfigurationRepository' => __DIR__ . '/../..' . '/classes/db/class.TemplateContainerConfigurationRepository.php',
        'CourseWizard\\DB\\WizardFlowRepository' => __DIR__ . '/../..' . '/classes/db/class.WizardFlowRepository.php',
        'CourseWizard\\Modal\\CourseTemplates\\ModalBaseCourseTemplate' => __DIR__ . '/../..' . '/classes/wizard_modal/templates/ModalBaseCourseTemplate.php',
        'CourseWizard\\Modal\\CourseTemplates\\ModalCourseTemplate' => __DIR__ . '/../..' . '/classes/wizard_modal/templates/ModalCourseTemplate.php',
        'CourseWizard\\Modal\\ModalDataController' => __DIR__ . '/../..' . '/classes/wizard_modal/ModalDataController.php',
        'CourseWizard\\Modal\\ModalPresenter' => __DIR__ . '/../..' . '/classes/wizard_modal/modal/ModalPresenter.php',
        'CourseWizard\\Modal\\Page\\BaseModalPagePresenter' => __DIR__ . '/../..' . '/classes/wizard_modal/page/BaseModalPagePresenter.php',
        'CourseWizard\\Modal\\Page\\ContentInheritancePage' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.ContentInheritancePage.php',
        'CourseWizard\\Modal\\Page\\IntroductionPage' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.IntroductionPage.php',
        'CourseWizard\\Modal\\Page\\JavaScriptPageConfig' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.JavaScriptPageConfig.php',
        'CourseWizard\\Modal\\Page\\ModalPagePresenter' => __DIR__ . '/../..' . '/classes/wizard_modal/page/ModalPagePresenter.php',
        'CourseWizard\\Modal\\Page\\QuitWizardPage' => __DIR__ . '/../..' . '/classes/wizard_modal/page/QuitWizardPage.php',
        'CourseWizard\\Modal\\Page\\SettingsPage' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.SettingsPage.php',
        'CourseWizard\\Modal\\Page\\StateMachine' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.StateMachine.php',
        'CourseWizard\\Modal\\Page\\TemplateSelectionPage' => __DIR__ . '/../..' . '/classes/wizard_modal/page/class.TemplateSelectionPage.php',
        'CourseWizard\\Modal\\RoundtripModalPresenter' => __DIR__ . '/../..' . '/classes/wizard_modal/modal/class.RoundtripModalPresenter.php',
        'CourseWizard\\Modal\\RoundtripWizardModalGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/modal/RoundtripWizardModalGUI.php',
        'CourseWizard\\Modal\\WizardModalFactory' => __DIR__ . '/../..' . '/classes/wizard_modal/class.WizardModalFactory.php',
        'CourseWizard\\Modal\\WizardModalGUI' => __DIR__ . '/../..' . '/classes/wizard_modal/modal/WizardModalGUI.php',
        'CourseWizard\\admin\\CourseTemplateContainerTableDataProvider' => __DIR__ . '/../..' . '/classes/admin/class.CourseTemplateContainerTableDataProvider.php',
        'CourseWizard\\admin\\CourseTemplateContainerTableGUI' => __DIR__ . '/../..' . '/classes/admin/class.CourseTemplateContainerTableGUI.php',
        'CourseWizard\\role\\RoleTemplateDefinition' => __DIR__ . '/../..' . '/classes/role/RoleTemplateDefinition.php',
        'ilCourseWizardApiGUI' => __DIR__ . '/../..' . '/classes/class.ilCourseWizardApiGUI.php',
        'ilCourseWizardConfigGUI' => __DIR__ . '/../..' . '/classes/class.ilCourseWizardConfigGUI.php',
        'ilCourseWizardGlobalScreenModificationProvider' => __DIR__ . '/../..' . '/classes/class.ilCourseWizardGlobalScreenModificationProvider.php',
        'ilCourseWizardPlugin' => __DIR__ . '/../..' . '/classes/class.ilCourseWizardPlugin.php',
        'ilObjCourseWizard' => __DIR__ . '/../..' . '/classes/class.ilObjCourseWizard.php',
        'ilObjCourseWizardAccess' => __DIR__ . '/../..' . '/classes/class.ilObjCourseWizardAccess.php',
        'ilObjCourseWizardContainerSettings' => __DIR__ . '/../..' . '/classes/class.ilObjCourseWizardContainerSettings.php',
        'ilObjCourseWizardGUI' => __DIR__ . '/../..' . '/classes/class.ilObjCourseWizardGUI.php',
        'ilObjCourseWizardListGUI' => __DIR__ . '/../..' . '/classes/class.ilObjCourseWizardListGUI.php',
        'ilObjCourseWizardTemplateManagementGUI' => __DIR__ . '/../..' . '/classes/course_template/management/class.ilObjCourseWizardTemplateManagementGUI.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit71f87bf9dc756d3f9fbef51b34a2eae1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit71f87bf9dc756d3f9fbef51b34a2eae1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit71f87bf9dc756d3f9fbef51b34a2eae1::$classMap;

        }, null, ClassLoader::class);
    }
}
