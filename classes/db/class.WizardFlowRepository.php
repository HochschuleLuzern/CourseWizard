<?php

namespace CourseWizard\DB;

class WizardFlowRepository
{
    const TABLE_NAME = 'rep_robj_xcwi_templates';

    const COL_ID = 'id';
    const COL_TARGET_OBJ_ID = 'target_obj_id';
    const COL_EXECUTING_USER = 'executing_user';
    const COL_SELECTED_TEMPLATE = 'selected_template';
    const COL_WIZARD_STATUS = 'wizard_status';

    /** @var \ilDBInterface */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;

        if(!$db->sequenceExists(self::TABLE_NAME)) {
            $db->createSequence(self::TABLE_NAME);
        }
    }

    public function createNewWizardFlow() {

    }
}