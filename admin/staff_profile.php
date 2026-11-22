<?php
// Staff profile page.
// Only STAFF accounts can open this page (includes/auth.php answers "Access denied." to everyone else).
$required_role = 'STAFF';
$pageTitle = 'My profile';
$section = 'Staff';
$profile_role = 'STAFF';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$profile_notice_key = 'notice';
include '../includes/profile_actions.php';
include '../includes/workspace.php';
include '../templates/profile.php';
include '../includes/end.php';
