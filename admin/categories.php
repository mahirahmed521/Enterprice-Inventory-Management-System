<?php
require_once __DIR__ . '/../includes/check_fields.php';
$pageTitle = 'Categories';
$section = 'Directory';
$error = '';
include '../includes/edit_request.php';
$action = '';
$name = '';
$parent_id = '';
$description = '';

if (isset($_POST['action'])) {
    $action = trim($_POST['action']);
}
if (isset($_POST['name'])) {
    $name = trim($_POST['name']);
}
if (isset($_POST['parent_id'])) {
    $parent_id = trim($_POST['parent_id']);
}
if (isset($_POST['description'])) {
    $description = trim($_POST['description']);
}

require '../includes/auth.php';
if ($supervisor == false) {
    exit('Access denied.');
}

if ($id > 0) {
    $records = mysqli_query($conn, "SELECT * FROM categories WHERE category_id=$id");
    $record = null;
    if ($records) {
        $record = mysqli_fetch_assoc($records);
    }
    if ($record == null) {
        exit('Record not found.');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $name = $record['name'];
        $parent_id = $record['parent_id'];
        $description = $record['description'];
    }
}

if ($id < 1) {
    if ($action == 'delete') {
        $action = '';
    }
}
if ($action == 'delete') {
    $deleted = mysqli_query($conn, "DELETE FROM categories WHERE category_id=$id");
    if ($deleted == false) {
        $error = 'This record is still in use.';
    } elseif (mysqli_affected_rows($conn) == 0) {
        $error = 'This record is still in use.';
    }
} elseif ($action == 'save') {
    $editor = 'edit';
    if ($name === '') {
        $error = 'Complete the required fields.';
    }
    $parent = 0;
    if (isset($parent_id[0])) {
        if (ctype_digit($parent_id) == false) {
            exit('Choose a valid parent category.');
        }
        $parent = $parent_id;
    }
    $visited = [];
    while ($parent > 0) {
        if ($error) {
            break;
        }
        if ($parent == $id) {
            $error = 'Choose a parent outside this category branch.';
        } elseif (isset($visited[$parent])) {
            $error = 'Choose a parent outside this category branch.';
        } else {
            $visited[$parent] = true;
            $parents = mysqli_query($conn, "SELECT parent_id FROM categories WHERE category_id=$parent");
            if ($parents == false) {
                $error = 'Could not check the parent category.';
                break;
            }
            $ancestor = mysqli_fetch_assoc($parents);
            if ($ancestor == null) {
                $error = 'Choose an existing category.';
            } else {
                $parent = $ancestor['parent_id'];
            }
        }
    }
    if ($error == '') {
        $sql_text = [
            'name' => $name,
            'description' => $description
        ];
        include __DIR__ . '/../includes/quote_fields.php';
        $parent_id_sql = 'NULL';
        if (isset($parent_id[0])) {
            if (ctype_digit($parent_id) == false) {
                exit('Choose a valid record.');
            }
            $parent_id_sql = $parent_id;
        }

        if ($id == 0) {
            $sql = "INSERT INTO categories (name, parent_id, description)
                VALUES ('$sql_text[name]', $parent_id_sql, '$sql_text[description]')";
        } else {
            $sql = "UPDATE categories SET name='$sql_text[name]',
                    parent_id=$parent_id_sql,
                    description='$sql_text[description]'
                    WHERE category_id=$id";
        }
        if (mysqli_query($conn, $sql) == false) {
            $error = 'Could not save. Check the category name and parent category.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = 'Choose a valid action.';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($error == '') {
        $_SESSION['notice'] = 'Record updated.';
        header('Location: categories.php');
        exit;
    }
}

$rows = mysqli_query($conn, 'SELECT category_id,name,description FROM categories ORDER BY category_id DESC');
include '../includes/workspace.php';
include '../templates/categories.php';
include '../includes/end.php';
