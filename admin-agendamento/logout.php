<?php
require_once __DIR__ . '/incluir/auth.php';
session_destroy();
header('Location: login.php');
exit;