<?php
$users = [
	//'username' => [ 'password' => 'SHA256_or_MD5_hash_of_password_here' ],
	'admin' => [ 'password' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8' ], // default password is password
];
$allow_proxy = true; // trust the HTTP_X_FORWARDED_FOR header for IP addresses
$session_timeout = 3600 * 24;



ini_set('session.gc_maxlifetime', $session_timeout);
ini_set('session.cookie_lifetime', $session_timeout);
session_set_cookie_params([
    'lifetime' => $session_timeout,
    'path' => '/',
    'domain' => '', // or your domain
    'secure' => true, // true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax' // or 'None' if needed
]);
unset($session_timeout);
session_start();
$remote = ($allow_proxy && isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
$hash = md5($_SERVER['HTTP_HOST'].'-'.getcwd().'-'.$remote);
$uri = (isset($_SERVER['SCRIPT_URL'])?$_SERVER['SCRIPT_URL']:$_SERVER['SCRIPT_NAME']);
$uri = str_replace('..', '', $uri);
if (empty($uri)) die('No URI detected');
if (!isset($_SESSION['pwprotect']) || !is_array($_SESSION['pwprotect']) || $_SESSION['pwprotect']['hash'] !== $hash) {
  $showform = true;
  if (isset($_POST['login'])) {
    if (empty($_POST['username'])) {
      echo 'Username can not be empty';
    } elseif (empty($_POST['password'])) {
      echo 'Password can not be empty';
    } elseif (!array_key_exists($_POST['username'], $users)) {
      trigger_error("Invalid username ($_POST[username]) $uri $_SERVER[REMOTE_ADDR]", E_USER_WARNING);
      echo 'Username or password is incorrect.';
    } else {
      $expected_hash = $users[$_POST['username']]['password'];
      $hash_matches = (
        strlen($expected_hash)==32
        ? ($expected_hash === md5($_POST['password']))
        : hash_equals($expected_hash, hash('sha256', $_POST['password']))
      );
      if (!$hash_matches) {
        trigger_error("Invalid password for $_POST[username] $uri $_SERVER[REMOTE_ADDR]", E_USER_WARNING);
        echo 'Username or password is incorrect.';
      } else {
        $username = strtolower($_POST['username']);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        trigger_error("Login Successful $username $uri $_SERVER[REMOTE_ADDR]", E_USER_WARNING);
        ini_set('display_errors', 1);
        $_SESSION['pwprotect'] = ['hash' => $hash, 'user' => $username];
        $showform = false;
      }
      unset($crypt, $pass, $expected_hash);
    }
  }
  if ($showform) {
echo <<<EOF
<form method="POST">
<input type="text" name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<input type="submit" name="login" value="Login">
</form>
EOF;
exit;
  }
  unset($hash, $remote);
}
if (isset($_GET['logout'])) {
  $_SESSION = array();
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
  $url = urldecode($_GET['logout']);
  if (empty($url))
    $url = substr($_SERVER['SCRIPT_URL'], 0, -strlen(basename($_SERVER['SCRIPT_URL'])));
  header('Location: '.$url);
  echo "<a href=\"$url\">$url</a>";
  exit;
}
if (!empty($_SERVER['SCRIPT_URL'])) require ".$uri";
