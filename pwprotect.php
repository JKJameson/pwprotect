<?php
$users = [
	//'username' => [ 'password' => 'MD5_hash_of_password_here' ],
	'admin' => [ 'password' => '5F4DCC3B5AA765D61D8327DEB882CF99' ], // default password is password
];

session_start();
$hash = md5($_SERVER['HTTP_HOST'].'-'.getcwd().'-'.$_SERVER['REMOTE_ADDR']);
$uri = $_SERVER['SCRIPT_URL'];
if (empty($uri)) $uri = $_SERVER['SCRIPT_NAME'];
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
    } elseif (strtolower($users[$_POST['username']]['password']) !== md5($_POST['password'])) {
      trigger_error("Invalid password for $_POST[username] $uri $_SERVER[REMOTE_ADDR]", E_USER_WARNING);
      echo 'Username or password is incorrect.';
    } else {
      $username = strtolower($_POST['username']);
      trigger_error("Login Successful $username $uri $_SERVER[REMOTE_ADDR]", E_USER_WARNING);
      $_SESSION['pwprotect'] = ['hash' => $hash, 'user' => $username];
      $showform = false;
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
}
if (isset($_GET['logout'])) {
  $_SESSION = array();
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
             );
  }
  session_destroy();
  $url = urldecode($_GET['logout']);
  if (empty($url))
    $url = substr($_SERVER['SCRIPT_URL'], 0, -strlen(basename($_SERVER['SCRIPT_URL'])));
  header('Location: '.$url);
  echo "<a href=\"$url\">$url</a>";
  exit;
}
require ".$uri";
