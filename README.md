# pwprotect
Password Protect Files using a PHP login page

An alternative to Apache's Password Protection for Directories.

+ Uses PHP sessions
+ Logs failed attempts to PHP error log
+ Logs successful logins to PHP error log

-------------------

+ The username of the logged in user is provided by the variable $_SESSION['pwprotect']['user']

-------------------

+ Logout a session by navigating to pwprotect.php?logout
+ Optionally, redirect to a custom URL by passing an urlencoded string like this pwprotect.php?logout=https%3A%2F%2Fgoogle.com (which would redirect to https://google.com

