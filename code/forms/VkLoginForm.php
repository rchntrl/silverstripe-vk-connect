<?php

/**
 * Class VkLoginForm
 * 
 */
class VkLoginForm extends MemberLoginForm {

    protected $authenticator_class = 'VkAuthenticator';

    public function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) {
        if($checkCurrentUser && Member::currentUser() && Member::logged_in_session_exists()) {
            $fields = new FieldList(
                new HiddenField("AuthenticationMethod", null, $this->authenticator_class, $this)
            );

            $actions = new FieldList(
                new FormAction("logout", _t('Member.BUTTONLOGINOTHER', "Log in as someone else"))
            );
        }
        else {
            $fields = new FieldList(
                new LiteralField('VkLoginIn', "<fb:login-button scope='". $controller->getVkPermissions() ."'></fb:login-button>")
            );

            $actions = new FieldList(
                new LiteralField('VkLoginLink', "<!-- <a href='".$controller->getVkLoginLink() ."'>". _t('VkLoginForm.LOGIN', 'Login') ."</a> -->")
            );
        }

        $backURL = (isset($_REQUEST['BackURL'])) ? $_REQUEST['BackURL'] : Session::get('BackURL');

        if(isset($backURL)) {
            $fields->push(new HiddenField('BackURL', 'BackURL', $backURL));
        }

        return parent::__construct($controller, $name, $fields, $actions);
    }
}
