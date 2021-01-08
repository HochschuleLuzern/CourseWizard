<?php

namespace CourseWizard\DB;

use CourseWizard\DB\Models\WizardFlow;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;

class WizardFlowRepository
{
    const TABLE_NAME = 'rep_robj_xcwi_wizard';

    //const COL_ID = 'id';
    const COL_TARGET_REF_ID = 'target_ref_id';
    const COL_EXECUTING_USER = 'executing_user';
    const COL_SELECTED_TEMPLATE = 'selected_template';
    const COL_WIZARD_STATUS = 'wizard_status';
    const COL_CURRENT_STEP = 'current_step';

    /** @var \ilDBInterface */
    protected $db;

    private $cache;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;

        if(!$db->sequenceExists(self::TABLE_NAME)) {
            $db->createSequence(self::TABLE_NAME);
        }

        $cache = array();
    }

    private function buildWizardFlowFromRow($row) : WizardFlow
    {
        switch($row[self::COL_CURRENT_STEP]) {
            case WizardFlow::STEP_INTRODUCTION:
                return WizardFlow::newWizardFlow($row[self::COL_TARGET_REF_ID]);
            case WizardFlow::STEP_TEMPLATE_SELECTION:
                return WizardFlow::wizardFlowWithSelectedTemplate(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_SELECTED_TEMPLATE]
                );
            case WizardFlow::STEP_CONTENT_INHERITANCE:
                return WizardFlow::wizardFlowWithContentInheritance(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_SELECTED_TEMPLATE],
                    null
                );
            case WizardFlow::STEP_SELECTED_SETTINGS:
            case WizardFlow::STEP_FINISHED_WIZARD:

            return WizardFlow::wizardFlowFinished(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_SELECTED_TEMPLATE],
                    null,
                    null
                );
            default:
                throw new \InvalidArgumentException("Wizard step with nr {$row[self::COL_CURRENT_STEP]} does not exist");
        }
    }

    public function createNewWizardFlow($crs_ref_id) : WizardFlow
    {
        $wizard_flow = WizardFlow::newWizardFlow($crs_ref_id);

        $this->db->insert(self::TABLE_NAME,
            array(
                self::COL_TARGET_REF_ID => array('integer', $wizard_flow->getCrsRefId()),
            ));
    }

    private function queryWizardFlowByCrs($crs_ref_id) : ?WizardFlow
    {
        $query = "SELECT * FROM {self::TABLE_NAME} WHERE crs_id = " . $this->db->quote($crs_ref_id, 'integer');
        $result = $this->db->query($query);
        if($row = $this->db->fetchAssoc($result)) {
            $this->cache[$crs_ref_id] = $this->buildWizardFlowFromRow($row);
            return $this->cache[$crs_ref_id];
        }

        return null;
    }

    public function wizardFlowForCrsExists($crs_ref_id) : bool
    {
        if(isset($this->cache[$crs_ref_id]) && $this->cache[$crs_ref_id] != null) {
            return true;
        } else if($this->queryWizardFlowByCrs($crs_ref_id) != null) {
            return true;
        }

        return false;
    }

    public function getWizardFlowForCrs($crs_ref_id) : WizardFlow
    {
        if($this->wizardFlowForCrsExists($crs_ref_id)) {
            if(isset($this->cache[$crs_ref_id])) {
                return $this->cache[$crs_ref_id];
            } else {
                return $this->queryWizardFlowByCrs($crs_ref_id);
            }
        } else {
            return $this->createNewWizardFlow($crs_ref_id);
        }
    }
    
    public function updateWizardFlow(WizardFlow $wizard_flow) {

        $update = array();
        $where = array(self::COL_TARGET_REF_ID => array('integer', $wizard_flow->getCrsRefId()));

        switch($wizard_flow->getCurrentStep()) {
            case WizardFlow::STEP_TEMPLATE_SELECTION:
                $update = array(
                    self::COL_SELECTED_TEMPLATE => array('integer', $wizard_flow->getTemplateSelection())
                );
        }

        $this->db->insert(
            self::TABLE_NAME,
            array(
                $update,
                $where
            )
        );
    }
}