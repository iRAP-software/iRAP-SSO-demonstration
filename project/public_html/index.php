<?php

session_start();

require_once(__DIR__ . '/../../settings/Settings.php');


if (!isset($_SESSION['user_id']))
{
    $gotSsoData = true;
    
    if (isset($_GET['user_data']))
    {
        $decodedUserJsonData = urldecode($_GET['user_data']);
        $userDataArray = json_decode($decodedUserJsonData, true);
        
        foreach (Settings::EXPECTED_SSO_PARAMS as $param)
        {
            if (!isset($userDataArray[$param]))
            {
                $gotSsoData = false;
                break;
            }
        }
    }
    else 
    {
        $gotSsoData = false;
    }
    
    
    if (!$gotSsoData)
    {
        $params = array('broker_id' => BROKER_ID);
        header("Location: " . SSO_SITE_HOSTNAME . "?" . http_build_query($params));
    }
    else
    {
        if (isValidSignature($userDataArray))
        {
            // Specifically set the session ID for the user. This way we can destroy
            // a session for a particular user from another script.
            // Cannot call session_id AFTER session_start if setting ID.
            session_destroy(); 
            session_id(generateSessionId($userDataArray['user_id']));
            session_start();
            
            $_SESSION['user_id']    = $userDataArray['user_id'];
            $_SESSION['user_name']  = $userDataArray['user_name'];
            $_SESSION['user_email'] = $userDataArray['user_email'];
            
            header("Location: " . SITE_HOSTNAME);
        }
        else
        {
            # Invalid request (hack?), redirect the user back to sign in.
            $params = array('broker_id' => BROKER_ID);
            header("Location: " . SSO_SITE_HOSTNAME . "?" . http_build_query($params));
        }
    }
}
else
{
    # User is signed in.
    print 
        "Hello " . $_SESSION['user_name'] . ". <br />" . 
        "Your user ID is: " . $_SESSION['user_id'] . " " . 
        "and your email is: " . $_SESSION['user_email'];
}


/**
 * Check whether the user details sent to us came from
 * the SSO service without being modified.
 * @param $dataArray - array of name/value pairs in the recieved data
 */
function isValidSignature($dataArray)
{
    if (!isset($dataArray['signature']))
    {
        throw new Exception("Missing signature");
    }
    
    $recievedSignature = $dataArray['signature'];
    unset($dataArray['signature']);
    ksort($dataArray);
    $jsonString = json_encode($dataArray);
    $generatedSignature = hash_hmac('sha256', $jsonString, BROKER_SECRET);
    
    return ($generatedSignature === $recievedSignature);
}


/**
 * Generate a session ID to use for a given user_id. We need to do this so
 * that we can figure out which file to destroy (to destroy the session) for
 * the appropriate user when we get a logout request for a specific user ID.
 * @param int $user_id - the ID of the user we are generating a session ID for.
 */
function generateSessionId($user_id)
{
    return hash_hmac('sha256', $user_id, BROKER_SECRET);
}