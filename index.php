<?php
require_once "vendor/autoload.php";

use IMSGlobal\LTI\ToolProvider;
use IMSGlobal\LTI\ToolProvider\DataConnector;
use IMSGlobal\LTI\OAuth\OAuthConsumer;
use IMSGlobal\LTI\OAuth\OAuthRequest;
use IMSGlobal\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use  IMSGlobal\LTI\OAuth\OAuthDataStore;
use IMSGlobal\LTI\OAuth\OAuthToken;
use IMSGlobal\LTI\OAuth\OAuthServer;

class ImsToolProvider extends ToolProvider\ToolProvider
{
    function onLaunch()
    {
        $tool_consumer_secrets['toolstest1'] = 'toolstest1!';
        $ok = true;
        // Check it is a POST request
        $ok = $ok && $_SERVER['REQUEST_METHOD'] === 'POST';
        // Check the LTI message type
        $ok = $ok && isset($_POST['lti_message_type']) && ($_POST['lti_message_type'] === 'basic-lti-launch-request');
        // Check the LTI version
        $ok = $ok && isset($_POST['lti_version']) && ($_POST['lti_version'] === 'LTI-1p0');
        // Check a consumer key exists
        $ok = $ok && !empty($_POST['oauth_consumer_key']);
        // Check a resource link ID exists
        $ok = $ok && !empty($_POST['resource_link_id']);
        // Check the consumer key is recognised
        $ok = $ok && array_key_exists($_POST['oauth_consumer_key'], $tool_consumer_secrets);
        // Check the OAuth credentials (nonce, timestamp and signature)
        if ($ok) {
            try {
                $consumer_key = $_POST['oauth_consumer_key'];
                $store = new ImsOAuthDataStore($consumer_key, 'toolstest1secret');
                $server = new OAuthServer($store);
                $method = new OAuthSignatureMethod_HMAC_SHA1();
                $server->add_signature_method($method);
                $request = OAuthRequest::from_request();
                $signature_key = $method->build_signature($request, $store->lookup_consumer($consumer_key), $store->lookup_token($consumer_key, '', ''));
                $server->verify_request($request);
                echo "<br>";
                print_r($signature_key);
                echo "<br>";
            } catch (Exception $e) {
                $ok = FALSE;
            }
        }

        // Check if the request method is POST or PUT (you can add other methods as needed)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {

            // Get the raw payload data from the request body
            $launchData = $_POST;

            // Extract the username from the launch data
            $username = isset($launchData['lis_person_name_given']) ? $launchData['lis_person_name_given'] : '';
            $resource_title = isset($launchData['resource_link_title']) ? urlencode($launchData['resource_link_title']) : '';
            $oath_ckey = isset($launchData['oauth_consumer_key']) ? urlencode($launchData['oauth_consumer_key']) : '';
            $roles = isset($launchData['roles']) ? urlencode($launchData['roles']) : '';
            $fullname = isset($launchData['lis_person_name_full']) ? urlencode($launchData['lis_person_name_full']) : '';
            $oath_sign = isset($launchData['oauth_signature']) ? ($launchData['oauth_signature']) : '';

            // Use the username
            echo "Hello, $username!. Welcome to $resource_title";
            $deelpink = "udptest://?resourceTitle=" . $resource_title . "&oath_ckey=" . $oath_ckey . "&roles=" . $roles . "&fullname=" . $fullname . "&oath_sign=" . $oath_sign;
        }
    }
}
class ImsOAuthDataStore extends OAuthDataStore
{

    private $consumer_key = NULL;
    private $consumer_secret = NULL;

    public function __construct($consumer_key, $consumer_secret)
    {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    function lookup_consumer($consumer_key)
    {
        return new OAuthConsumer($this->consumer_key, $this->consumer_secret);
    }

    function lookup_token($consumer, $token_type, $token)
    {
        return new OAuthToken($consumer, '');
    }

    function lookup_nonce($consumer, $token, $nonce, $timestamp)
    {
        return FALSE;  // If a persistent store is available nonce values should be retained for a period and checked here
    }

    function new_request_token($consumer, $callback = null)
    {
        return NULL;
    }

    function new_access_token($token, $consumer, $verifier = null)
    {
        return NULL;
    }
}

// Cancel any existing session
session_start();
$_SESSION = array();
session_destroy();
session_start();

$db = mysql_connect("localhost:3307", "root", "");
if (!$db) {
    die('Not connected : ' . mysql_error());
}
$db_selected = mysql_select_db("ltitoolsdb", $db);
if (!$db_selected) {
    die('Can\'t use ltitools : ' . mysql_error());
}
$db_connector = DataConnector\DataConnector::getDataConnector('', $db, "mysql"); //need to specify the type of connector, in this case i use mysql not mysqli
$tool = new ImsToolProvider($db_connector);
$tool->onLaunch();

// $tool->handleRequest();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Rick and Morty API Example</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <h1>Rick and Morty Characters</h1>
        <div class="row" id="characterList">
            <!-- Characters will be dynamically added here -->
        </div>
    </div>
    <script>
        // Function to fetch characters from the API and update the HTML
        function fetchCharacters() {
            $.ajax({
                url: "https://rickandmortyapi.com/api/character/",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Clear previous data
                    $("#characterList").empty();

                    // Loop through characters and create HTML elements
                    data.results.forEach(function(character) {
                        var characterCard = `
              <div class="col-md-4 mb-3">
                <div class="card">
                  <img src="${character.image}" class="card-img-top" alt="${character.name}">
                  <div class="card-body">
                    <h5 class="card-title">${character.name}</h5>
                    <p class="card-text">Status: ${character.status}</p>
                    <p class="card-text">Species: ${character.species}</p>
                  </div>
                </div>
              </div>
            `;
                        $("#characterList").append(characterCard);
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                }
            });
        }

        // Load characters on page load
        $(document).ready(function() {
            fetchCharacters()
            console.log("ready!");
        });
    </script>
</body>

</html>