<?php

namespace CourseWizard\role;

class RoleTemplatesDefinition
{
    public const ROLE_TPL_TITLE_CONTAINER_ADMIN = 'xcwi_container_admin';
    public const ROLE_TPL_TITLE_CONTENT_CREATOR = 'xcwi_container_content_creator';
    public const ROLE_TPL_TITLE_CRS_IMPORTER = 'xcwi_t_crs_importer';

    public const CONF_KEY_ROLT_CONTAINER_ADMIN = 'rolt_container_admin';
    public const CONF_KEY_ROLT_CONTENT_CREATOR = 'rolt_content_creator';
    public const CONF_KEY_ROLT_CRS_IMPORTER = 'rolt_crs_importer';

    private $title;
    private $description;
    private $conf_key;

    /**
     * RoleTemplateDefinition constructor.
     * @param string $title
     * @param string $description
     * @param string $conf_key
     */
    public function __construct(string $title, string $description, string $conf_key)
    {
        $this->title = $title;
        $this->description = $description;
        $this->conf_key = $conf_key;
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


    public static function getRoleTemplateDefinitions() : array
    {
        $rolt_definitions = array();

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CONTAINER_ADMIN,
            '',
            self::CONF_KEY_ROLT_CONTAINER_ADMIN);

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CONTENT_CREATOR,
            '',
            self::CONF_KEY_ROLT_CONTENT_CREATOR);

        $rolt_definitions[] = new RoleTemplatesDefinition(
            self::ROLE_TPL_TITLE_CRS_IMPORTER,
            '',
            self::CONF_KEY_ROLT_CRS_IMPORTER);

        return $rolt_definitions;
    }
}