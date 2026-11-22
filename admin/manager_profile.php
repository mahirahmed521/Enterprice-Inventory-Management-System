<?php
// Manager profile page.
// Only MANAGER accounts can open this page (includes/auth.php answers "Access denied." to everyone else).
$required_role = 'MANAGER';
$pageTitle = 'My profile';
$section = 'Manager';
$profile_role = 'MANAGER';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$profile_notice_key = 'notice';
include '../includes/profile_actions.php';
include '../includes/workspace.php';
include '../templates/profile.php';
include '../includes/end.php';
