<?php
session_start();
session_destroy();
echo "<h1>Logged out</h1>";
echo "<p><a href=\"/quick-login.php\">Login again</a></p>";
?>