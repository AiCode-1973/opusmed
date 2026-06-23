<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /opusmed/public/login.php');
exit;
