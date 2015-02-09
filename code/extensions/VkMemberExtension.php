<?php

/**
 * @package vkconnect
 */
class VkMemberExtension extends DataExtension {
    
    /**
     * @var array
     */
    private static $db = array(
        'Email' => 'Varchar(255)',
        'VkUID' => 'Varchar(200)',
        'VkLink' => 'Varchar(200)',
        'VkTimezone' => 'Varchar(200)',
        'VkAccessToken' => 'Varchar'
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->makeFieldReadonly('VkUID');
        $fields->makeFieldReadonly('VkLink');
        $fields->makeFieldReadonly('VkTimezone');
    }

    /**
     * Sync the new data from a users Vk profile to the member database.
     *
     * @param mixed $result
     * @param bool $sync Flag to whether we override fields like first name
     */
    public function updateVkFields($result, $override = true) {
        /** @var Member $member */
        $member = $this->owner;
        $member->VkUID = $result->uid;
        if($override) {
            $session = Session::get(VkControllerExtension::VK_ACCESS_TOKEN);
            $email = $session->email;
            $member->Nickname = $member->Nickname ?: ($result->nickname ?: $result->screen_name);
            if($email && !$this->owner->Email || !Email::validEmailAddress($this->owner->Email)) {
                $member->Email = $email;
            }
            $member->FirstName = $member->FirstName ?: $result->first_name;
            $member->Surname = $member->Surname ?: $result->last_name;
            $member->VkTimezone = $member->VkTimezone ?: $result->timezone;
        }

        $member->extend('onUpdateVkFields', $result);
    }

    /**
     * @param stdClass $info
     */
    public function syncVkDetails($info) {
        $sync = Config::inst()->get('VkControllerExtension', 'sync_member_details');
        $create = Config::inst()->get('VkControllerExtension', 'create_member');

        $this->owner->updateVkFields($info, $sync);

        // sync details	to the database
        if(($this->owner->ID && $sync) || $create) {
            if($this->owner->isChanged()) {
                $this->owner->write();
            }
        }

        // ensure members are in the correct groups
        if($groups = Config::inst()->get('VkControllerExtension', 'member_groups')) {
            foreach($groups as $group) {
                $this->owner->addToGroupByCode($group);
            }
        }
    }
}
