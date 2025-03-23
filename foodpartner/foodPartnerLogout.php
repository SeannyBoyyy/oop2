<?php
session_start();
session_destroy();
header("Location: foodPartnerLogin.php");
exit();
?>