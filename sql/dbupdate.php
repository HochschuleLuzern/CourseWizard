<#1>
<?php
$current_table = \CourseWizard\DB\CourseTemplateRepository::TABLE_NAME;
if(!$ilDB->tableExists($current_table))
{
    $fields = array(
        \CourseWizard\DB\CourseTemplateRepository::COL_TEMPLATE_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_CRS_REF_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_CRS_OBJ_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_TEMPLATE_TYPE => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default'=> 0
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_STATUS_CODE => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default'=> 0
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_CREATOR_USER_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => false,
            'default'=> 0
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_CREATE_DATE => array(
            'type' => 'timestamp',
            'notnull' => false
        ),
        \CourseWizard\DB\CourseTemplateRepository::COL_TEMPLATE_CONTAINER_REF_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true,
            'default'=> 0
        ),
    );

    $ilDB->createTable($current_table, $fields);
    $ilDB->addPrimaryKey($current_table, array(\CourseWizard\DB\CourseTemplateRepository::COL_TEMPLATE_ID));

}

if(!$ilDB->sequenceExists($current_table)) {
    $ilDB->createSequence($current_table);
}

?>
<#2>
<?php
$current_table = \CourseWizard\DB\PluginConfigKeyValueStore::TABLE_NAME;
if(!$ilDB->tableExists($current_table))
{
    $fields = array(
        \CourseWizard\DB\PluginConfigKeyValueStore::COL_KEY => array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true,
        ),
        \CourseWizard\DB\PluginConfigKeyValueStore::COL_VALUE => array(
            'type' => 'text',
            'length' => 4000,
            "notnull" => false,
            "default" => null
        ));

    $ilDB->createTable($current_table, $fields);
    $ilDB->addPrimaryKey($current_table, array(\CourseWizard\DB\PluginConfigKeyValueStore::COL_KEY));
}
?>
<#3>
<?php
$current_table = \CourseWizard\DB\TemplateContainerConfigurationRepository::TABLE_NAME;
if(!$ilDB->tableExists($current_table))
{
    $fields = array(
        \CourseWizard\DB\TemplateContainerConfigurationRepository::COL_OBJ_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\TemplateContainerConfigurationRepository::COL_IS_GLOBAL => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        \CourseWizard\DB\TemplateContainerConfigurationRepository::COL_ROOT_LOCATION_REF_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\TemplateContainerConfigurationRepository::COL_RESPONSIBLE_ROLE_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        )
    );

    $ilDB->createTable($current_table, $fields);
    $ilDB->addPrimaryKey($current_table, array(\CourseWizard\DB\TemplateContainerConfigurationRepository::COL_OBJ_ID));
}
?>
<#4>
<?php
$current_table = \CourseWizard\DB\WizardFlowRepository::TABLE_NAME;
if(!$ilDB->tableExists($current_table))
{
    $fields = array(
        \CourseWizard\DB\WizardFlowRepository::COL_TARGET_REF_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\WizardFlowRepository::COL_EXECUTING_USER => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\WizardFlowRepository::COL_SELECTED_TEMPLATE => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => false,
            'default'=> null
        ),
        \CourseWizard\DB\WizardFlowRepository::COL_WIZARD_STATUS => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ),
        \CourseWizard\DB\WizardFlowRepository::COL_FIRST_OPEN_TS => array(
            'type' => 'timestamp',
            'notnull' => true
        ),
        \CourseWizard\DB\WizardFlowRepository::COL_FINISHED_IMPORT_TS => array(
            'type' => 'timestamp',
            'notnull' => false
        )
    );

    $ilDB->createTable($current_table, $fields);
    $ilDB->addPrimaryKey($current_table, array(\CourseWizard\DB\WizardFlowRepository::COL_TARGET_REF_ID));
}
?>
<#5>
<?php
$current_table = \CourseWizard\DB\UserPreferencesRepository::TABLE_NAME;
if(!$ilDB->tableExists($current_table))
{
    $fields = array(
        \CourseWizard\DB\UserPreferencesRepository::COL_USER_ID => array(
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ),
        \CourseWizard\DB\UserPreferencesRepository::COL_SKIP_INTRO => array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0,
            'notnull' => true
        ),
        \CourseWizard\DB\UserPreferencesRepository::COL_SKIP_INTRO_DATE => array(
            'type' => 'timestamp',
            'notnull' => false,
            "default" => null
        )
	);

    $ilDB->createTable($current_table, $fields);
    $ilDB->addPrimaryKey($current_table, array(\CourseWizard\DB\UserPreferencesRepository::COL_USER_ID));
}
?>