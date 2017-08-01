<?php

session_start();

require_once(__DIR__ . '/../../settings/Settings.php');


$ssoClient = new iRAP\SsoClient\SsoClient(BROKER_ID, BROKER_SECRET);

if (!isset($_SESSION['user_id']))
{
    /*
     * If the user isn't logged in, create a SsoClient object and run the login() method.
     * The user's browser will be redirected to the SSO and then returned here with the user
     * credentials.
     */
    $ssoDetails = $ssoClient->login();

    if($ssoClient->loginSuccessful())
    {
        /*
         * The SsoClient provides a session id that can be used to identify the user, even from
         * outside of the current session. This is useful when remotely destroying the session.
         */
        session_destroy();
        session_id($ssoDetails->get_session_id());
        session_start();
        
        $_SESSION['user_id'] = $ssoDetails->get_user_id();
        $_SESSION['sso_expiry'] = $ssoDetails->get_sso_expiry();
    }
}

if (isset($_SESSION['sso_expiry']) && $_SESSION['sso_expiry'] < time())
{
    /*
     * When SSO expiry time is passed, the user should be redirected to the SSO, to keep its
     * session alive. The user will be instantly returned to here.
     */

    $ssoExpiry = $ssoClient->renewSSOSession($returnData);

    if($ssoExpiry->get_sso_expiry())
    {
        /*
         * The new SSO Expiry time should be saved, in order to trigger the next redirect.
         */
        $_SESSION['sso_expiry'] = $ssoExpiry->get_sso_expiry();
    }
}