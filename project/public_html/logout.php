<?php

/*
 * This is the callback handler to handle logout requests from the SSO.
 * Remember that this is being called by the server and should destroy a user's session, and
 * is not a user trying to destroy their own session.
 */

session_start();

require_once(__DIR__ . '/../../settings/Settings.php');


function main()
{
    try
    {
        if (isset($_GET['data']))
        {
            $decodedUserJsonData = urldecode($_GET['data']);
            $dataArray = json_decode($decodedUserJsonData, true);
            
            if (!isset($dataArray['signature']))
            {
                throw Exception("Missing required signature");
            }
            
            if (!isset($dataArray['user_id']))
            {
                throw Exception("Missing required user ID");
            }
            
            if (!isset($dataArray['time']))
            {
                throw Exception("Missing required timestamp");
            }
            
            # check the timestamp is recent so we dont suffer from replay attacks.
            date_default_timezone_set('UTC'); 
            
            if (microtime($get_as_float=true) - $dataArray['time'] > REQUEST_MAX_AGE)
            {
                throw new Exception("Request is out of date.");
            }
            
            # Check the signature is valid (so we know request actually came from sso.irap.org)
            if (isValidSignature($dataArray))
            {
                $session_id = generateSessionId($dataArray['user_id']);
                $session_filepath = session_save_path() . '/' . 'sess_' . $session_id;
                
                if (file_exists($session_filepath))
                {
                    $deletedFile = unlink($session_filepath);
                    
                    if ($deletedFile)
                    {
                        $responseArray = array(
                            "result"  => "success",
                            "message" => "User wasn't logged in."
                        );
                    }
                    else
                    {
                        $responseArray = array(
                            "result"  => "error",
                            "message" => "Failed to destroy user's session."
                        );
                    }
                }
                else
                {
                    $responseArray = array(
                        "result"  => "success",
                        "message" => "User wasn't logged in."
                    );
                }
            }
            else
            {
                # Invalid request (hack?), redirect the user back to sign in.
                throw new Exception("Invalid signature.");
            }
        }
        else 
        {
            throw new Exception("Missing required data parameter");
        }
    }
    catch (Exception $e)
    {
        $responseArray = array(
            "result"  => "error",
            "message" => $e->getMessage()
        );
    }
    
    print json_encode($responseArray);
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

    if ($generatedSignature !== $recievedSignature)
    {
        print print_r($dataArray, true);
        die("$generatedSignature !== $recievedSignature");
    }
    
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


main();