<?php

class ilCourseWizardPlugin extends ilRepositoryObjectPlugin
{
    public const ID = 'xcwi';

    public function __construct()
    {

        parent::__construct();

        global $DIC;
        require_once $this->getDirectory() . '/vendor/autoload.php';
        if($DIC->isDependencyAvailable('globalScreen'))
        {
            $this->provider_collection->setModificationProvider(new ilCourseWizardGlobalScreenModificationProvider($DIC, $this));
        }
    }

    public function getPluginName()
    {
        return 'CourseWizard';
    }

    protected function uninstallCustom()
    {
        // TODO: Implement uninstallCustom() method.
    }
}