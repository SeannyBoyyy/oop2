<?php
session_start();
session_destroy();
header("Location: cinemaOwnerLogin.php");
exit();
?>