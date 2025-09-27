<?php
session_start();
session_unset();
session_destroy();
require_once __DIR__ . '/includes/config.php';
header('Location: ' . getPortalUrl('login.php'));
exit;