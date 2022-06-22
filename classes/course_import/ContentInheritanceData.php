<?php declare(strict_types = 1);

class ContentInheritanceData
{
    public const IMPORT_OPTION_COPY = 'copy';
    public const IMPORT_OPTION_LINK = 'link';
    public const IMPORT_OPTION_OMIT = 'omit';

    private $ref_id;
    private $import_option;
    private $child_objects;

    private $available_import_options = array(self::IMPORT_OPTION_COPY, self::IMPORT_OPTION_LINK, self::IMPORT_OPTION_OMIT);

    public function __construct(int $ref_id, string $import_option, $child_objects = array())
    {
        if (!in_array($import_option, $this->available_import_options)) {
            throw new InvalidArgumentException("Argument '$import_option' is not an available import option");
        }

        $this->ref_id = $ref_id;
        $this->import_option = $import_option;
        $this->child_objects = $child_objects;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getImportOption() : string
    {
        return $this->import_option;
    }

    public function getChildObjects() : array
    {
        return $this->getChildObjects();
    }
}
