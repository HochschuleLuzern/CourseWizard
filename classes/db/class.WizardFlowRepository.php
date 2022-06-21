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
                    new \DateTimeImmutable($row[self::COL_FIRST_OPEN_TS]),
                    $row[self::COL_WIZARD_STATUS]
                );

            case WizardFlow::STATUS_IMPORTING:
                return WizardFlow::wizardFlowImporting(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    new \DateTimeImmutable($row[self::COL_FIRST_OPEN_TS]),
                    $row[self::COL_SELECTED_TEMPLATE]
                );
            case WizardFlow::STATUS_QUIT:
                return WizardFlow::quitedWizardFlow(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    new \DateTimeImmutable($row[self::COL_FIRST_OPEN_TS]),
                    new \DateTimeImmutable($row[self::COL_FINISHED_IMPORT_TS])
                );

            case WizardFlow::STATUS_FINISHED:
                return WIzardFLow::finishedWizardFlow(
                    $row[self::COL_TARGET_REF_ID],
                    $row[self::COL_EXECUTING_USER],
                    new \DateTimeImmutable($row[self::COL_FIRST_OPEN_TS]),
                    $row[self::COL_SELECTED_TEMPLATE],
                    new \DateTimeImmutable($row[self::COL_FINISHED_IMPORT_TS])
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
                self::COL_WIZARD_STATUS => array('integer', $wizard_flow->getCurrentStatus()),
                self::COL_FIRST_OPEN_TS => array('timestamp', $wizard_flow->getFirstOpen()->format('Y-m-d H:i:s'))
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
            return $this->createNewWizardFlow($crs_ref_id, $this->executing_user->getId());
        }
    }
    
    public function updateWizardFlowStatus(WizardFlow $wizard_flow)
    {
        $where = array(self::COL_TARGET_REF_ID => array('integer', $wizard_flow->getCrsRefId()));
        $update_fields = array(
            self::COL_SELECTED_TEMPLATE => array('integer', $wizard_flow->getTemplateSelection()),
            self::COL_WIZARD_STATUS => array('integer', $wizard_flow->getCurrentStatus()),

        );

        if(!is_null($wizard_flow->getExecutingUser())) {
            $update_fields[self::COL_EXECUTING_USER] = array('integer', $wizard_flow->getExecutingUser());
        }

        if(!is_null($wizard_flow->getFirstOpen())) {
            $update_fields[self::COL_FIRST_OPEN_TS] = array('timestamp', $wizard_flow->getFirstOpen()->format('Y-m-d H:i:s'));
        }

        if(!is_null($wizard_flow->getFinishedImport())) {
            $update_fields[self::COL_FINISHED_IMPORT_TS] = array('timestamp', $wizard_flow->getFinishedImport()->format('Y-m-d H:i:s'));
        }

        $this->db->update(
            self::TABLE_NAME,
            $update_fields,
            $where
        );

        $this->cache[$wizard_flow->getCrsRefId()] = $wizard_flow;
    }
}
