<?php declare(strict_types = 1);

namespace CourseWizard\role;

class RoleTemplatesDefinition
{
    public const ROLE_TPL_TITLE_CONTAINER_ADMIN = 'xcwi_t_container_admin';
    public const ROLE_TPL_TITLE_CONTENT_CREATOR = 'xcwi_t_container_content_creator';
    public const ROLE_TPL_TITLE_CRS_IMPORTER = 'xcwi_t_crs_importer';

    public const ROLE_TPL_DESCRIPTION_CONTAINER_ADMIN = 'role_tpl_description_container_admin';
    public const ROLE_TPL_DESCRIPTION_CONTENT_CREATOR = 'role_tpl_description_content_creator';
    public const ROLE_TPL_DESCRIPTION_CRS_IMPORTER = 'role_tpl_description_crs_importer';

    public const CONF_KEY_ROLT_CONTAINER_ADMIN = 'rolt_container_admin';
    public const CONF_KEY_ROLT_CONTENT_CREATOR = 'rolt_content_creator';
    public const CONF_KEY_ROLT_CRS_IMPORTER = 'rolt_crs_importer';

    public const DEFAULT_PERMISSIONS_CONTAINER_ADMIN = array(
        'visible_read' => array('crs', 'xcwi', 'bibl', 'blog', 'book', 'copa', 'dcl', 'exc', 'file', 'fold', 'frm', 'glo', 'grp', 'htlm', 'lm', 'iass', 'itgr', 'lso', 'mcst', 'spl', 'qpl', 'sahs', 'sess', 'svy', 'tst', 'webr', 'wiki'),
        'write' => array('xcwi'),
        'copy' => array(),
        'create_objects' => array()
    );
    public const DEFAULT_PERMISSIONS_CONTENT_CREATOR = array(
        'visible_read' => array('xcwi'),
        'write' => array(),
        'copy' => array(),
        'create_objects' => array('xcwi' => array('crs'))
    );
    public const DEFAULT_PERMISSIONS_CRS_IMPORTER = array(
        'visible_read' => array('crs', 'xcwi', 'bibl', 'blog', 'book', 'copa', 'dcl', 'exc', 'file', 'fold', 'frm', 'glo', 'grp', 'htlm', 'lm', 'iass', 'itgr', 'lso', 'mcst', 'spl', 'qpl', 'sahs', 'sess', 'svy', 'tst', 'webr', 'wiki'),
        'write' => array(),
        'copy' => array('bibl', 'blog', 'book', 'copa', 'dcl', 'exc', 'file', 'fold', 'frm', 'glo', 'grp', 'htlm', 'lm', 'iass', 'itgr', 'lso', 'mcst', 'spl', 'qpl', 'sahs', 'sess', 'svy', 'tst', 'webr', 'wiki'),
        'create_objects' => array()
    );

    public const DEFAULT_ROLE_TPL_CRS_TEMPLATE_EDITOR = 'il_crs_admin';
    public const DEFAULT_ROLE_TPL_CRS_NO_MEMBER = 'il_crs_non_member';

    private string $title;
    private string $description;
    private string $conf_key;
    private bool $is_protected;
    private array $default_permissions;

    public function __construct(string $title, string $description, string $conf_key, bool $is_protected = false, array $default_permissions = array())
    {
        $this->title = $title;
        $this->description = $description;
        $this->conf_key = $conf_key;
        $this->is_protected = $is_protected;

        if (count($default_permissions) == 0) {
            $this->default_permissions = array(
                'visible_read' => array(),
                'write' => array(),
                'copy' => array(),
                'create_objects' => array()
            );
        } else {
            $this->default_permissions = $default_permissions;
        }
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getConfKey() : string
    {
        return $this->conf_key;
    }

    /**
     * @return bool
     */
    public function isProtected() : bool
    {
        return $this->is_protected;
    }

    public function checkDefaultPermissionByOperationName($type, string $operation_name) : bool
    {
        try {
            switch ($operation_name) {
                case 'visible':
                case 'read':
                    return in_array($type, $this->default_permissions['visible_read']);

                case 'write':
                    return in_array($type, $this->default_permissions['write']);

                case 'copy':
                    return in_array($type, $this->default_permissions['copy']);

                default:
                    if (substr($operation_name, 0, 7) == 'create_' && array_key_exists($type, $this->default_permissions['create_object'])) {
                        // Remove the 'create_' part from the string
                        $create_subobj = substr($operation_name, 7);

                        return in_array($create_subobj, $this->default_permissions['create_object'][$type]);
                    } else {
                        return false;
                    }
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    public static function getRoleTemplateDefinitions() : array
    {
        $rolt_definitions = array();

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CONTAINER_ADMIN,
            self::ROLE_TPL_DESCRIPTION_CONTAINER_ADMIN,
            self::CONF_KEY_ROLT_CONTAINER_ADMIN,
            true,
            self::DEFAULT_PERMISSIONS_CONTAINER_ADMIN
        );

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CONTENT_CREATOR,
            self::ROLE_TPL_DESCRIPTION_CONTENT_CREATOR,
            self::CONF_KEY_ROLT_CONTENT_CREATOR,
            false,
            self::DEFAULT_PERMISSIONS_CONTENT_CREATOR
        );

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CRS_IMPORTER,
            self::ROLE_TPL_DESCRIPTION_CRS_IMPORTER,
            self::CONF_KEY_ROLT_CRS_IMPORTER,
            false,
            self::DEFAULT_PERMISSIONS_CRS_IMPORTER
        );

        return $rolt_definitions;
    }
}
