# pwprotect
Password Protect Files using a PHP login page

An alternative to Apache's Password Protection for Directories.

+ Uses PHP sessions
+ Logs failed attempts to PHP error log
+ Logs successful logins to PHP error log

-------------------

+ The username of the logged in user is provided by the variable $_SESSION['pwprotect']['user']