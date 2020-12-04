<?php

namespace CourseWizard\DB;

class PluginConfigKeyValueStore
{
    const TABLE_NAME = 'rep_robj_xcwi_pl_conf';

    const COL_KEY = 'key';
    const COL_VALUE = 'value';

    /** @var \ilDBInterface */
    private $db;

    /** @var array */
    private $data_cache;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
        $this->data_cache = array();
    }

    public function get(string $key) : ?string
    {
        if(isset($this->data_cache[$key])) {
            return $this->data_cache[$key];
        }

        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::COL_KEY . '=' . $this->db->quote($key, \ilDBConstants::T_TEXT);
        $result = $this->db->query($query);

        if($data = $this->db->fetchAssoc($result)) {
            $this->data_cache[$key] = $data[self::COL_VALUE];
            return $data[self::COL_VALUE];
        }

        return NULL;
    }

    public function set(string $key, string $value)
    {
        if($this->get($key) == NULL) {
            $this->insertNewKeyValuePair($key, $value);
        } else {
            $this->updateExistingKeyValuePair($key, $value);
        }

        $this->data_cache[$key] = $value;
    }

    public function delete(string $key)
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . self::COL_KEY . ' = ' . $this->db->quote($key, \ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    private function insertNewKeyValuePair(string $key, string $value)
    {
        $this->db->insert(
            // INSERT INTO
            self::TABLE_NAME,

            // VALUES
            array(
                self::COL_KEY => array(\ilDBConstants::T_TEXT, $key),
                self::COL_VALUE => array(\ilDBConstants::T_TEXT, $value)
            )
        );
    }

    private function updateExistingKeyValuePair(string $key, string $value)
    {
        $this->db->update(
            // UPDATE
            self::TABLE_NAME,

            // SET value = $value
            array(self::COL_VALUE => array(\ilDBConstants::T_TEXT, $value)),

            // WHERE key = $key
            array(self::COL_KEY => array(\ilDBConstants::T_TEXT, $key))
        );
    }
}