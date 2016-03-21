<?php

session_start();

require_once(__DIR__ . '/Settings.php');



if (!isset($_SESSION['user_id']))
{
    $gotSsoData = true;
    
    foreach (Settings::EXPECTED_SSO_PARAMS as $param)
    {
        if (!isset($_GET[$param]))
        {
            $gotSsoData = false;
            break;
        }
    }
    
    if ($gotSsoData)
    {
        header("Location: sso.irap.org?broker_id=" . BROKER_ID);
    }
    else
    {
        if (isValidSignature(Settings::EXPECTED_SSO_PARAMS))
        {
            $_SESSION['user_id']    = $_GET['user_id'];
            $_SESSION['user_name']  = $_GET['user_name'];
            $_SESSION['user_email'] = $_GET['user_email'];
            
            header("Location: sso.irap.org?broker_id=" . BROKER_ID);
        }
        else
        {
            # Invalid request (hack?), redirect the user back to sign in.
            header("Location: sso.irap.org?broker_id=" . BROKER_ID);
        }
    }
}
else
{
    # User is signed in.
    print 
        "Hello " . $_SESSION['user_name'] . ". " . 
        "Your user ID is: " . $_SESSION['user_id'] . " " . 
        "and your email is: " . $_SESSION['user_email'];
}


/**
 * Check whether the user details sent to us came from
 * sso.irap.org unmodified.
 * @param $expectedSsoParams - array list of expected $_GET parameters sent by sso.irap.org
 */
function isValidSignature($expectedSsoParams)
{
    $data = array();
    
    foreach ($expectedSsoParams as $paramName)
    {
        $data[$paramName] = $_GET[$paramName];
    }
    
    unset($data['siganture']);
    ksort($data);
    $dataString = json_encode($data);
    $sig = hash_hmac('sha256', $dataString, BROKER_SECRET);

    return ($sig === $_GET['signature']);
}



