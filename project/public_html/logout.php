<?php

/*
 * This is the callback handler to handle logout requests from the SSO.
 * Remember that this is being called by the server and should destroy a user's session, and
 * is not a user trying to destroy their own session.
 */

session_start();

require_once(__DIR__ . '/../../settings/Settings.php');


$ssoClient = new iRAP\SsoClient\SsoClient(BROKER_ID, BROKER_SECRET);

$ssoLogout = $ssoClient->logoutWebhook();

if (isset($ssoLogout->session_id))
{
    $session_file = session_save_path() . 'sess_' . $ssoLogout->session_id;
    
    if (file_exists($session_file))
    {
        $session_destroyed = unlink($session_file);
        
        if ($session_destroyed)
        {
            $responseArray = array(
                "result"  => "success",
                "message" => "Session destroyed."
            );
            
            echo json_encode($responseArray);
     
        }
        
    }
    
}