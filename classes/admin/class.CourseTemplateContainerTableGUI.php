<?php declare(strict_types = 1);

namespace CourseWizard\admin;

class CourseTemplateContainerTableGUI extends \ilTable2GUI
{
    const TABLE_ID = 'crs_template_overview_table';
    const FORM_NAME = 'crs_template_overview_table';

    const COL_CONTAINER_TITLE = 'container_title';
    const COL_ROOT_LOCATION_TITLE = 'root_location_title';
    const COL_QUANTITY_CRS_TEMPLATES = 'quantity_crs_templates';
    const COL_ROLE_TITLE = 'role_title';
    const COL_QUANTITY_ADMIN_ROLE_MEMBERS = 'quantity_admin_role_members';
    const COL_IS_GLOBAL = 'is_global';
    const COL_ACTION_DROPDOWN = 'action';

    protected array $change_status_modals;

    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;
    private \ilCourseWizardPlugin $plugin;

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

        $this->setTitle($this->plugin->txt('conf_container_table_title'));
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $a_command));

        $this->setRowTemplate('tpl.dep_config_row.html', $this->plugin->getDirectory());

        $this->change_status_modals = array();
    }

    protected function getColumnDefinition() : array
    {
        return array(
            array(
                'field' => self::COL_CONTAINER_TITLE,
                'txt' => $this->plugin->txt('template_container'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_ROOT_LOCATION_TITLE,
                'txt' => $this->plugin->txt('root_location_of_wizard'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_QUANTITY_CRS_TEMPLATES,
                'txt' => $this->plugin->txt('quantity_crs_templates'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_QUANTITY_ADMIN_ROLE_MEMBERS,
                'txt' => $this->plugin->txt('quantity_admin_role_members'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_IS_GLOBAL,
                'txt' => $this->plugin->txt('global_available'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            ),

            array(
                'field' => self::COL_ACTION_DROPDOWN,
                'txt' => $this->plugin->txt('action'),
                'default' => true,
                'optional' => false,
                'sortable' => false
            )
        );
    }

    final protected function fillRow($row): void
    {
        foreach ($this->getColumnDefinition() as $index => $column) {
            switch ($column['field']) {
                case self::COL_CONTAINER_TITLE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_CONTAINER_TITLE]);
                    break;

                case self::COL_ROOT_LOCATION_TITLE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_ROOT_LOCATION_TITLE]);
                    break;

                case self::COL_ROLE_TITLE:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_ROLE_TITLE]);
                    break;

                case self::COL_QUANTITY_CRS_TEMPLATES:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_QUANTITY_CRS_TEMPLATES]);
                    break;

                case self::COL_QUANTITY_ADMIN_ROLE_MEMBERS:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_QUANTITY_ADMIN_ROLE_MEMBERS]);
                    break;

                case self::COL_IS_GLOBAL:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_IS_GLOBAL]);
                    break;

                case self::COL_ACTION_DROPDOWN:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', $row[self::COL_ACTION_DROPDOWN]);
                    $this->change_status_modals[] = isset($row['modal']) ? $row['modal'] : null;
                    break;
                default:
                    $this->tpl->setCurrentBlock('column');
                    $this->tpl->setVariable('COLUMN_VALUE', 'Column ' . $column['field'] . ' does not exist yet');
                    break;
            }

            $this->tpl->parseCurrentBlock();
        }
    }
}
