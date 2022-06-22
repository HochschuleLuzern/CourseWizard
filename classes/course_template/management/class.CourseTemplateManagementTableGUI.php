<?php declare(strict_types = 1);

namespace CourseWizard\CourseTemplate;

class CourseTemplateManagementTableGUI extends \ilTable2GUI
{
    const TABLE_ID = 'department_crs_template_table';
    const FORM_NAME = 'department_crs_template_table';

    const COL_TEMPLATE_TITLE = 'template_title';
    const COL_TEMPLATE_DESCRIPTION = 'template_description';
    const COL_TEMPLATE_TYPE = 'template_type';
    const COL_PROPOSAL_DATE = 'suggested_date';
    const COL_STATUS = 'status';
    const COL_ACTION_DROPDOWN = 'action';

    protected $change_status_modals;

    public function __construct($a_parent_obj, string $a_command, \ilCourseWizardPlugin $plugin)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_command);

        $this->plugin = $plugin;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->setId(self::TABLE_ID);
        $this->setFormName(self::FORM_NAME);

        $columns = $this->getColumnDefinition();
        foreach ($this->getColumnDefinition() as $index => $column) {
            $this->addColumn(
                $column['txt'],
                isset($column['sortable']) && $column['sortable'] ? $column['filed'] : '',
                isset($column['width']) ? $column['width'] : '',
                isset($column['is_checkbox']) ? (bool) $column['is_checkbox'] : false
            );
        }

        // TODO: Add title as lang var
        $this->setTitle('Table Title');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $a_command));

        $this->setRowTemplate('tpl.dep_config_row.html', $this->plugin->getDirectory());

        $this->change_status_modals = array();
    }

    protected function getColumnDefinition()
    {
        // TODO: Add language here
        return array(
            array(
                'field' => self::COL_TEMPLATE_TITLE,
                'txt' => 'Template Bezeichnung',
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_TEMPLATE_DESCRIPTION,
                'txt' => 'Beschreibung',
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_PROPOSAL_DATE,
                'txt' => 'Vorgeschlagen am...',
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_STATUS,
                'txt' => 'Status',
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_ACTION_DROPDOWN,
                'txt' => $this->lng->txt('action'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            )
        );
    }


    private function createLink($title, string $link)
    {
        return "<a href='$link'>$title</a>";
    }

    final protected function fillRow($row)
    {
        global $DIC;
        foreach ($this->getColumnDefinition() as $index => $column) {
            switch ($column['field']) {
                case self::COL_TEMPLATE_TITLE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_TEMPLATE_TITLE]);
                    break;

                case self::COL_TEMPLATE_DESCRIPTION:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_TEMPLATE_DESCRIPTION]);
                    break;

                case self::COL_TEMPLATE_TYPE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_TEMPLATE_TYPE]);
                    break;

                case self::COL_PROPOSAL_DATE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_PROPOSAL_DATE]);
                    break;

                case self::COL_STATUS:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_STATUS]);
                    break;

                case self::COL_ACTION_DROPDOWN:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_ACTION_DROPDOWN]);
                    $this->change_status_modals[] = $row['modal'];
                    break;
                default:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', 'Column ' . $column['field'] . ' does not exist yet');
                    break;
            }

            $this->tpl->parseCurrentBlock();
        }
    }

    public function getHTML()
    {
        global $DIC;
        return parent::getHTML() . $DIC->ui()->renderer()->render($this->change_status_modals); // TODO: Change the autogenerated stub
    }
}
