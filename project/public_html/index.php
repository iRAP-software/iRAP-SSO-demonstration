<?php

session_start();

require_once(__DIR__ . '/../Settings.php');


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
        $params = array(
            'broker_id' => BROKER_ID,
            'broker_public_key' => BROKER_PUBLIC_KEY
        );
        
        header("Location: " . SSO_SITE_HOSTNAME . "?" . http_build_query($params));
    }
    else
    {
        if (isValidSignature(Settings::EXPECTED_SSO_PARAMS))
        {
            $_SESSION['user_id']    = $_GET['user_id'];
            $_SESSION['user_name']  = $_GET['user_name'];
            $_SESSION['user_email'] = $_GET['user_email'];
            
            header("Location: " . SITE_HOSTNAME);
        }
        else
        {
            # Invalid request (hack?), redirect the user back to sign in.
            $params = array(
                'broker_id' => BROKER_ID,
                'broker_public_key' => BROKER_PUBLIC_KEY
            );
            
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