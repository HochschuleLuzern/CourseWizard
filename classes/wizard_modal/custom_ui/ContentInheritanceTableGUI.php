<?php declare(strict_types = 1);

namespace CourseWizard\CustomUI;

/**
 * Class ContentInheritanceTableGUI
 *
 * This class is kind of a copy from ilObjectCopySelectionTableGUI
 *
 * @package CourseWizard\CustomUI
 */
class ContentInheritanceTableGUI extends \ilTable2GUI
{
    protected \ilObjUser $user;
    protected \ilObjectDefinition $obj_definition;
    protected \ilTree $tree;
    protected \ilAccessHandler $access;

    private string $type = '';
    private bool $try_to_default_omit_subgroups;

    public function __construct($a_parent_class, string $a_parent_cmd, string $a_type, bool $hide_subgroups)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->try_to_default_omit_subgroups = true;

        parent::__construct($a_parent_class, $a_parent_cmd);
        $this->type = $a_type;

        $this->lng = $lng;

        $this->addColumn($this->lng->txt('title'), '', '55%');
        $this->addColumn($this->lng->txt('copy'), '', '15%');
        $this->addColumn($this->lng->txt('link'), '', '15%');
        $this->addColumn($this->lng->txt('omit'), '', '15%');

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.obj_copy_selection_row.html", "Services/Object");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setLimit(999999);

        $this->setFormName('cmd');
    }

    public function getType()
    {
        return $this->type;
    }

    public function parseSource(int $a_source)
    {
        $tree = $this->tree;
        $objDefinition = $this->obj_definition;
        $ilAccess = $this->access;

        $first = true;

        $is_in_subgroup = false;

        $crs_title = \ilObject::_lookupTitle(\ilObject::_lookupObjectId($a_source));
        foreach ($tree->getSubTree($root = $tree->getNodeData($a_source)) as $node) {
            if ($node['type'] == 'rolf') {
                continue;
            }
            if (!$ilAccess->checkAccess('visible', '', $node['child'])) {
                continue;
            }

            $current_depth = $node['depth'] - $root['depth'];
            if($this->try_to_default_omit_subgroups && !$first){

                // Is object direct child of course
                // and is it of the type group
                // and has it a matching title to the course?
                if ($current_depth == 1
                    && $node['type'] == 'grp'
                    && (substr($node['title'], 0, strlen($node['title']) - 2) == $crs_title)
                ) {
                    $is_in_subgroup = true;
                    //continue;
                } else if ($is_in_subgroup && $current_depth > 1) {
                    //continue;
                } else {
                    $is_in_subgroup = false;
                }
            }

            $r = array();
            $r['last'] = false;
            $r['source'] = $first;
            $r['ref_id'] = $node['child'];
            $r['depth'] = $current_depth;
            $r['type'] = $node['type'];
            $r['title'] = $node['title'];
            $r['copy'] = $objDefinition->allowCopy($node['type']);
            $r['perm_copy'] = $ilAccess->checkAccess('copy', '', $node['child']);
            $r['link'] = $objDefinition->allowLink($node['type']);
            $r['perm_link'] = true;
            $r['is_in_subgroup'] = $is_in_subgroup;

            // #11905
            if (!trim($r['title']) && $r['type'] == 'sess') {
                // use session date as title if no object title
                include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
                $app_info = \ilSessionAppointment::_lookupAppointment($node["obj_id"]);
                $r['title'] = \ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']);
            }

            $rows[] = $r;

            $first = false;
        }
        $rows[] = array('last' => true);
        $this->setData((array) $rows);
    }

    /**
     * @see ilTable2GUI::fillRow()
     */
    protected function fillRow($s)
    {
        if ($s['last']) {
            $this->tpl->setCurrentBlock('footer_copy');
            $this->tpl->setVariable('TXT_COPY_ALL', $this->lng->txt('copy_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_link');
            $this->tpl->setVariable('TXT_LINK_ALL', $this->lng->txt('link_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_omit');
            $this->tpl->setVariable('TXT_OMIT_ALL', $this->lng->txt('omit_all'));
            $this->tpl->parseCurrentBlock();
            return true;
        }


        for ($i = 0; $i < $s['depth']; $i++) {
            $this->tpl->touchBlock('padding');
            $this->tpl->touchBlock('end_padding');
        }
        $this->tpl->setVariable('TREE_IMG', \ilObject::_getIcon(\ilObject::_lookupObjId($s['ref_id']), "tiny", $s['type']));
        $this->tpl->setVariable('TREE_ALT_IMG', $this->lng->txt('obj_' . $s['type']));
        $this->tpl->setVariable('TREE_TITLE', $s['title']);

        if ($s['source']) {
            return true;
        }

        // Copy
        if ($s['perm_copy'] and $s['copy']) {
            $this->tpl->setCurrentBlock('radio_copy');
            $this->tpl->setVariable('TXT_COPY', $this->lng->txt('copy'));
            $this->tpl->setVariable('NAME_COPY', 'cp_options[' . $s['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_COPY', \ilCopyWizardOptions::COPY_WIZARD_COPY);
            $this->tpl->setVariable('ID_COPY', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_copy');
            if(!isset($s['is_in_subgroup']) || (!$s['is_in_subgroup'])) {
                $this->tpl->setVariable('COPY_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($s['copy']) {
            $this->tpl->setCurrentBlock('missing_copy_perm');
            $this->tpl->setVariable('TXT_MISSING_COPY_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }


        // Link
        if ($s['perm_link'] and $s['link']) {
            $this->tpl->setCurrentBlock('radio_link');
            $this->tpl->setVariable('TXT_LINK', $this->lng->txt('link'));
            $this->tpl->setVariable('NAME_LINK', 'cp_options[' . $s['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_LINK', \ilCopyWizardOptions::COPY_WIZARD_LINK);
            $this->tpl->setVariable('ID_LINK', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_link');
            if (!$s['copy'] or !$s['perm_copy']) {
                $this->tpl->setVariable('LINK_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($s['link']) {
            $this->tpl->setCurrentBlock('missing_link_perm');
            $this->tpl->setVariable('TXT_MISSING_LINK_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Omit
        $this->tpl->setCurrentBlock('omit_radio');
        $this->tpl->setVariable('TXT_OMIT', $this->lng->txt('omit'));
        $this->tpl->setVariable('NAME_OMIT', 'cp_options[' . $s['ref_id'] . '][type]');
        $this->tpl->setVariable('VALUE_OMIT', \ilCopyWizardOptions::COPY_WIZARD_OMIT);
        $this->tpl->setVariable('ID_OMIT', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_omit');
        if (((!$s['copy'] || !$s['perm_copy']) && (!$s['link'])) || $s['is_in_subgroup']) {
            $this->tpl->setVariable('OMIT_CHECKED', 'checked="checked"');
        }
        $this->tpl->parseCurrentBlock();
    }

    public function getHTML()
    {
        return parent::getHTML();
    }
}
