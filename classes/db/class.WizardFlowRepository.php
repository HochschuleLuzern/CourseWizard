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
    const COL_SELECTED_TEMPLATE = 'selected_template_ref_id';
    const COL_WIZARD_STATUS = 'wizard_status';
    const COL_CURRENT_STEP = 'current_step';
    const COL_FIRST_OPEN_TS = 'first_open_ts';
    const COL_FINISHED_IMPORT_TS = 'finished_import_ts';

    /** @var \ilDBInterface */
    protected $db;

    /** @var \ilObjUser */
    protected $executing_user;

    private $cache;

    public function __construct(\ilDBInterface $db, \ilObjUser $executing_user)
    {
        $this->db = $db;
        $this->executing_user = $executing_user;

        if (!$db->sequenceExists(self::TABLE_NAME)) {
            $db->createSequence(self::TABLE_NAME);
        }

        $cache = array();
    }

    private function buildWizardFlowFromRow($row) : WizardFlow
    {
        switch ($row[self::COL_WIZARD_STATUS]) {
            case WizardFlow::STATUS_IN_PROGRESS:
            case WizardFlow::STATUS_POSTPONED:
                return WizardFlow::unfinishedWizardFlow(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    $row[self::COL_FIRST_OPEN_TS],
                    $row[self::COL_WIZARD_STATUS]
                );
                break;

            case WizardFlow::STATUS_IMPORTING:
                return WizardFlow::wizardFlowWithContentInheritance(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_SELECTED_TEMPLATE],
                    null
                );
            case WizardFlow::STATUS_QUIT:
                return WizardFlow::quitedWizardFlow(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    $row[self::COL_FIRST_OPEN_TS],
                    $row[self::COL_FINISHED_IMPORT_TS]
                );

            case WizardFlow::STATUS_FINISHED:
                return WIzardFLow::finishedWizardFlow(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    $row[self::COL_FIRST_OPEN_TS],
                    $row[self::COL_SELECTED_TEMPLATE],
                    $row[self::COL_FINISHED_IMPORT_TS]
                );

            default:
                throw new \InvalidArgumentException("Wizard step with nr {$row[self::COL_CURRENT_STEP]} does not exist");
        }
    }

    public function createNewWizardFlow($crs_ref_id, $executing_user) : WizardFlow
    {
        $wizard_flow = WizardFlow::newlyCreatedWizardFlow($crs_ref_id, $executing_user);

        $this->db->insert(
            self::TABLE_NAME,
            array(
                self::COL_TARGET_REF_ID => array('integer', $wizard_flow->getCrsRefId()),
                self::COL_WIZARD_STATUS => array('integer', $wizard_flow->getCurrentStatus())
            )
        );

        return $wizard_flow;
    }

    private function queryWizardFlowByCrs($crs_ref_id) : ?WizardFlow
    {
        $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::COL_TARGET_REF_ID . " = " . $this->db->quote($crs_ref_id, 'integer');
        $result = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($result)) {
            $this->cache[$crs_ref_id] = $this->buildWizardFlowFromRow($row);
            return $this->cache[$crs_ref_id];
        }

        return null;
    }

    public function wizardFlowForCrsExists($crs_ref_id) : bool
    {
        if (isset($this->cache[$crs_ref_id]) && $this->cache[$crs_ref_id] != null) {
            return true;
        } elseif ($this->queryWizardFlowByCrs($crs_ref_id) != null) {
            return true;
        }

        return false;
    }

    public function getWizardFlowForCrs($crs_ref_id) : WizardFlow
    {
        if ($this->wizardFlowForCrsExists($crs_ref_id)) {
            if (isset($this->cache[$crs_ref_id])) {
                return $this->cache[$crs_ref_id];
            } else {
                return $this->queryWizardFlowByCrs($crs_ref_id);
            }
        } else {
            return $this->createNewWizardFlow($crs_ref_id, $this->executing_user);
        }
    }
    
    public function updateWizardFlowStatus(WizardFlow $wizard_flow)
    {
        $update = array();
        $where = array(self::COL_TARGET_REF_ID => array('integer', $wizard_flow->getCrsRefId()));

        $update = array(
            self::COL_SELECTED_TEMPLATE => array('integer', $wizard_flow->getTemplateSelection()),
            self::COL_WIZARD_STATUS => array('integer', $wizard_flow->getCurrentStatus())
        );

        $this->db->update(
            self::TABLE_NAME,
            $update,
            $where
        );
    }
}
