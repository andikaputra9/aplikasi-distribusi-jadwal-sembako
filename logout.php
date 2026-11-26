<?php

require_once __DIR__ . '/app/controllers/authcontroller.php';

$auth = new AuthController();
$auth->logout();

header('Location: login_view.php');
exit;
