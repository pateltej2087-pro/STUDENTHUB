<?php

ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");

$isHttps =
    isset($_SERVER["HTTPS"]) &&
    $_SERVER["HTTPS"] !== "off";

session_name("studenthub_session");

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/StudentHub/",
    "secure" => $isHttps,
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

header("Cache-Control: no-store");

function destroyUserSession()
{
    $_SESSION = [];

    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"]
    ]);

    session_destroy();
}