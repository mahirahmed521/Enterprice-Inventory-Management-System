<?php
// Administrator profile page.
// Only ADMIN accounts can open this page (includes/auth.php answers "Access denied." to everyone else).
$required_role = 'ADMIN';
$pageTitle = 'My profile';
$section = 'Administrator';
$profile_role = 'ADMIN';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$profile_notice_key = 'notice';
include '../includes/profile_actions.php';
include '../includes/workspace.php';
include '../templates/profile.php';
include '../includes/end.php';
