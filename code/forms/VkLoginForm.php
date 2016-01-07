<?php

/**
 * @package vkconnect
 * TODO complete VkloginForm
 */
class VkLoginForm extends MemberLoginForm
{

    protected $authenticator_class = 'VkAuthenticator';

    public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true)
    {
        if ($checkCurrentUser && Member::currentUser() && Member::logged_in_session_exists()) {
        } else {
        }

        $backURL = (isset($_REQUEST['BackURL'])) ? $_REQUEST['BackURL'] : Session::get('BackURL');

        if (isset($backURL)) {
            $fields->push(new HiddenField('BackURL', 'BackURL', $backURL));
        }

        return parent::__construct($controller, $name, $fields, $actions);
    }
}
