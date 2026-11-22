<?php
// Post / Redirect / Get safety net.
//
// Why this exists: when a page is the direct answer to a form POST (for example a form that shows
// "Enter a name" under the field), the browser cannot rebuild that page from its cache. Pressing Back
// then shows ERR_CACHE_MISS / "Confirm Form Resubmission".
//
// What it does: after any POST that would have shown a page, it saves that page in the session,
// answers with a 303 redirect to the same address, and the next GET shows the saved page once.
// POSTs that already redirect (the normal "saved, go to the list" case), file downloads, and
// non-HTML answers are left alone. It is loaded by config/db.php, so every page gets it.

if (defined('PRG_LOADED')) {
    return;
}
define('PRG_LOADED', 1);
if (PHP_SAPI == 'cli') {
    return;
}

$prg_method = 'GET';
if (isset($_SERVER['REQUEST_METHOD'])) {
    $prg_method = $_SERVER['REQUEST_METHOD'];
}

if ($prg_method == 'GET' && isset($_COOKIE[session_name()])) {
    // Show the page saved by the POST that just redirected here, once.
    $prg_started = false;
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        $prg_started = true;
    }
    if (isset($_SESSION['prg_page']) && is_array($_SESSION['prg_page'])) {
        $prg_saved = $_SESSION['prg_page'];
        unset($_SESSION['prg_page']);
        if ($prg_saved['uri'] == $_SERVER['REQUEST_URI'] && time() - $prg_saved['time'] < 120) {
            session_write_close();
            header('Content-Type: text/html; charset=utf-8');
            echo $prg_saved['html'];
            exit;
        }
    }
    if ($prg_started) {
        session_write_close();
    }
}

function prg_finish($level)
{
    // Fold any inner buffers into ours. If a page already flushed ours, leave everything alone.
    while (ob_get_level() > $level) {
        ob_end_flush();
    }
    if (ob_get_level() < $level) {
        return;
    }
    if (headers_sent()) {
        ob_end_flush();
        return;
    }
    $skip = false;
    $sent = headers_list();
    $h = 0;
    while (isset($sent[$h])) {
        $line = strtolower($sent[$h]);
        if (strpos($line, 'location:') === 0 || strpos($line, 'refresh:') === 0 || strpos($line, 'content-disposition:') === 0) {
            $skip = true;
        }
        if (strpos($line, 'content-type:') === 0 && strpos($line, 'text/html') === false) {
            $skip = true;
        }
        $h = $h + 1;
    }
    if (http_response_code() != 200) {
        $skip = true;
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        $skip = true;
    }
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false && strpos($_SERVER['HTTP_ACCEPT'], '*/*') === false) {
        $skip = true;
    }
    $html = ob_get_contents();
    if ($skip || $html === false || strlen($html) == 0 || strlen($html) > 1500000) {
        ob_end_flush();
        return;
    }
    if (session_status() != PHP_SESSION_ACTIVE) {
        if (@session_start() == false) {
            ob_end_flush();
            return;
        }
    }
    $_SESSION['prg_page'] = array('uri' => $_SERVER['REQUEST_URI'], 'html' => $html, 'time' => time());
    ob_end_clean();
    header('Location: ' . $_SERVER['REQUEST_URI'], true, 303);
}

if ($prg_method == 'POST') {
    ob_start();
    register_shutdown_function('prg_finish', ob_get_level());
}
