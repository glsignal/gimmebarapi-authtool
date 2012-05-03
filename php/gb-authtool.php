#!/usr/bin/env php
<?php
require dirname(__FILE__)."/vendor/Resty.php";

define('CREDS_FILE', "./.gimmebar_user");

/**
 * First, try to load the client id and secret from the config file
 */
define('CONFIG_FILE', __DIR__ . "/config.json");
define('CONFIG_FILE_SAMPLE', __DIR__ . "/config.sample.json");

if (!file_exists(CONFIG_FILE)) {
	echo "No config file exists. Copy ".CONFIG_FILE_SAMPLE." to " .CONFIG_FILE. " and modify\n";
	die();
}

$conf = json_decode(file_get_contents(CONFIG_FILE));

/**
 * initialize the Resty object
 */
$resty = new Resty;
$resty->setBaseUrl($conf->API_BASE_URL);
if (!empty($conf->DEBUG)) {
	$resty->debug(true);
}


/**
 * FIRST STEP: Generate a request token
 */
$resp = $resty->post('auth/token/request', array(
					'type'=>'app',
					'client_id'=>$conf->CLIENT_ID,
					'client_secret'=>$conf->CLIENT_SECRET
				));
$rtok = $resp['body'];

if ($resp['status'] !== 200) {
	echo "Error(s) getting token. Response follows:\n";
	print_r($resp);
	die();
}

$req_token = $rtok->request_token;
echo "Got request token...\n";



/**
 * SECOND STEP: Send the user to the Gimme Bar web site to approve the app
 */

// generate the URL
$auth_url = "{$conf->SITE_BASE_URL}authorize?client_id={$conf->CLIENT_ID}&token={$req_token}&response_type=code";

// prompt to hit ENTER to continue
echo "Hit ENTER to open URL in browser and authenticate on Gimme Bar site. When you're finished, come back here.\n";
$input = fgets(STDIN); // waits for ENTER

// try to open the URL in a browser from the shell
echo "Trying to open <{$auth_url}> ...\nIf this doesn't work, visit the URL manually in a GUI web browser...\n";
shell_exec("open ".escapeshellarg($auth_url));

// tell the user to come back here when he/she is done
echo "Hit ENTER when you've give the app permission to access your account...\n";
$input = fgets(STDIN); // waits for ENTER

/**
 * THIRD STEP: Exchange our now-approved request token for an authorization token
 */
$resp = $resty->post('auth/token/authorization', array(
						'response_type'=>'code',
						'client_id'=>$conf->CLIENT_ID,
						'token'=>$req_token,
					));

if ($resp['status'] !== 200) {
	echo "Error(s) getting authorization. Response follows:\n";
	print_r($resp);
	die();
}

$auth_token = $resp['body']->code;


/**
 * FOURTH STEP: Exchange the authorization token for an access token
 */
$resp = $resty->post('auth/token/access', array(
							'code'=>$auth_token,
							'grant_type'=>'authorization_code',
						));

if ($resp['status'] !== 200) {
	echo "Error(s) getting authorization. Response follows:\n";
	print_r($resp);
	die();
}

$username = $resp['body']->username;
$access_token = $resp['body']->access_token;

echo "Access token retrieved for user {$username}. Testing authentication with Gimme Bar API...\n";

/**
 * Test our credentials
 */
$resty->setCredentials($username, $access_token);
$resp = $resty->get('auth/test');

echo "Response from auth/test:\n";
echo $resp['status'] . "\n";
print_r($resp['headers']);
print_r($resp['body']);

if ($resp['status'] === 200) {
	echo "SUCCESS!!!\n";
	echo "Trying to save credentials to file...\n";
	$fp = file_put_contents(CREDS_FILE, "{$username}:{$access_token}");
	if ($fp) {
		echo "Saved to " . realpath(CREDS_FILE) . "\n";
	} else {
		echo "Failed to write to file " . realpath(CREDS_FILE) . "\n";
	}

} else {
echo "auth fail!!!\n";
}

