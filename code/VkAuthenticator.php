<?php

/**
 *  @package vkconnect
 */
class VkAuthenticator  extends Authenticator {

    /**
     * Authentication is handled by Facebook rather than us this needs to
     * return the new member object which is created. Creation of the member
     * is handled by {@link FacebookConnect::onBeforeInt()}
     *
     * @return false|Member
     */
    public static function authenticate($RAW_data, Form $form = null) {
        return ($member = Member::currentUser()) ? $member : false;
    }

    /**
     * Return the Facebook login form
     *
     * @return Form
     */
    public static function get_login_form(Controller $controller) {
        return Object::create("VkLoginForm", $controller, "VkLoginForm");
    }

    /**
     * Return the name for the Facebook tab
     *
     * @return string
     */
    public static function get_name() {
        return _t('VkAuthenticator.TITLE', "Vk Connect");
    }
}
