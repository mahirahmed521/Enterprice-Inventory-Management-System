<?php
$names = array_keys($sql_text);
$field_number = 0;
while (isset($names[$field_number])) {
    $field = $names[$field_number];
    $value = $sql_text[$field];
    $quoted = '';
    $letter = 0;
    while (isset($value[$letter])) {
        if ($value[$letter] == "'") {
            $quoted = $quoted . "''";
        } else {
            $quoted = $quoted . $value[$letter];
        }
        $letter = $letter + 1;
    }
    $sql_text[$field] = $quoted;
    $field_number = $field_number + 1;
}
