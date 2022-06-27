<?php declare(strict_types = 1);

namespace CourseWizard\DB\Models;

class TemplateContainerConfiguration
{
    private int $obj_id;
    private int $root_location_ref_id;
    private int $responsible_role_id;
    private bool $is_global;

    public function __construct(int $obj_id, int $root_location_ref_id, int $responsible_role_id, bool $is_global)
    {
        $this->obj_id = $obj_id;
        $this->root_location_ref_id = $root_location_ref_id;
        $this->responsible_role_id = $responsible_role_id;
        $this->is_global = $is_global;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getRootLocationRefId() : int
    {
        return $this->root_location_ref_id;
    }

    public function getResponsibleRoleId() : int
    {
        return $this->responsible_role_id;
    }

    public function isGlobal() : bool
    {
        return $this->is_global;
    }

    public function withGlobalScope() : TemplateContainerConfiguration
    {
        $obj = clone $this;
        $obj->root_location_ref_id = 1;
        $obj->is_global = true;
        return $obj;
    }

    public function withLimitedScope(int $root_location_ref_id) : TemplateContainerConfiguration
    {
        $obj = clone $this;
        $obj->root_location_ref_id = $root_location_ref_id;
        $obj->is_global = false;
        return $obj;
    }
}
