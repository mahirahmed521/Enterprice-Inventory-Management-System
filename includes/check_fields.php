<?php
$submitted_values = array_merge(array_values($_GET), array_values($_POST));
$position = 0;
while (isset($submitted_values[$position])) {
    if (is_array($submitted_values[$position])) {
        exit('Enter one value in each field.');
    }
    $position = $position + 1;
}
