<?php
session_start();
header('Location: admin/' . (isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'));
exit;
