<?php declare(strict_types = 1);

namespace CourseWizard\DB;

use CourseWizard\DB\Models\UserPreferences;

class UserPreferencesRepository
{
    const TABLE_NAME = 'rep_robj_xcwi_usr_pref';

    //const COL_ID = 'id';
    const COL_USER_ID = 'user_id';
    const COL_SKIP_INTRO = 'skip_intro';
    const COL_SKIP_INTRO_DATE = 'skip_intro_clicked_date';

    /** @var \ilDBInterface */
    protected $db;

    /** @var array */
    protected $data_cache;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
        $this->data_cache = array();
    }

    private function buildPreferenceObjectWithDefaultValues(\ilObjUser $user)
    {
        return new UserPreferences(
            (int) $user->getId(),
            false,
            null
        );
    }

    private function buildPreferenceObjectFromDBRow($row) : UserPreferences
    {
        $user_pref = new UserPreferences(
            (int) $row[self::COL_USER_ID],
            $row[self::COL_SKIP_INTRO] == 1,
            $row[self::COL_SKIP_INTRO_DATE]
        );

        return $user_pref;
    }

    public function createNewUserPreferencesEntry(\ilObjUser $user) : UserPreferences
    {
        $user_pref = $this->buildPreferenceObjectWithDefaultValues($user);

        $this->db->insert(
            self::TABLE_NAME,
            array(
                self::COL_USER_ID => array('integer', $user_pref->getUserId()),
                self::COL_SKIP_INTRO => array('integer', $user_pref->wasSkipIntroductionsClicked() ? 1 : 0),
                self::COL_SKIP_INTRO_DATE => array('timestamp', $user_pref->getSkipIntroductionsClickedDate())
            )
        );

        return $user_pref;
    }

    public function getUserPreferences(\ilObjUser $user, bool $create_if_not_exists = true) : ?UserPreferences
    {
        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::COL_USER_ID . '=' . $this->db->quote($user->getId(), 'integer');
        $result = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($result)) {
            return $this->buildPreferenceObjectFromDBRow($row);
        } elseif ($create_if_not_exists) {
            return $this->createNewUserPreferencesEntry($user);
        }

        return null;
    }

    public function updateUserPreferences(UserPreferences $user_pref)
    {
        $this->db->update(
            self::TABLE_NAME,
            array(self::COL_SKIP_INTRO => array('integer', $user_pref->wasSkipIntroductionsClicked() ? '1' : '0'),
                  self::COL_SKIP_INTRO_DATE => array('timestamp', $user_pref->getSkipIntroductionsClickedDate())
            ),
            array(self::COL_USER_ID => array('integer', $user_pref->getUserId()))
        );
    }
}
