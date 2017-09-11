<?php

require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';

session_cache_limiter('nocache');
session_name("SID");
session_start();

$client = new Google_Client();
$oauth2 = new Google_Oauth2Service($client);
if (isset($_GET['code'])){
    $client->authenticate();
    $_SESSION['token'] = $client->getAccessToken();
    $user = $oauth2->userinfo->get();
    $_SESSION["google"]=Array(
        "gmail"=>$user["email"],
        "first"=>$user["given_name"],
        "last"=>$user["family_name"]
    );
}
?>
<html>
<body onload="document.getElementById('login_form').submit();">
<form id='login_form' action='<?php print($_SESSION["returnpoint"]);?>' method='post'>
    $content .= "<input type='hidden' name='action' value='login'/>";
    $content .= "</form>";
</body>
</html>
