<?php

/**
 * @package vkconnect
 *
 * @method string getVkCallbackLink
 * @method stdClass getAccessToken
 * @method stdClass getUserInfo
 *
 */
class VkConnectAuthCallback extends Controller
{

    private static $allowed_actions = array(
        'connect'
    );

    public function connect()
    {
        if (!$member = Member::currentUser()) {
            /** @var stdClass $params */
            $params = $this->getAccessToken($this->request->getVar('code'));
            // member is not currently logged into SilverStripe. Look up
            // for a member with the UID which matches first.
            $member = Member::get()->filter(array(
                "VkUID" => $params->user_id
            ))->first();

            if (!$member) {
                // see if we have a match based on email. From a
                // security point of view, users have to confirm their
                // email address in facebook so doing a match up is fine
                $email = $params->email;

                if ($email) {
                    $member = Member::get()->filter(array(
                        'Email' => $email
                    ))->first();
                }
            }

            if (!$member) {
                $member = Injector::inst()->create('Member');
                $member->syncVkDetails($this->getUserInfo());
            }
        }
        $member->logIn(true);

        // redirect the user to the provided url, otherwise take them
        // back to the route of the website.
        if ($url = Session::get(VkControllerExtension::SESSION_REDIRECT_URL_FLAG)) {
            return $this->redirect($url);
        } else {
            return $this->redirect(Director::absoluteBaseUrl());
        }
    }
}
