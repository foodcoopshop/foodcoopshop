<?php
foreach ($values['files'] as $key => $value) {
    if (preg_match('/\.php$/i', $value)) {
        continue;
    }
    if (preg_match('/\.ctp$/i', $value)) {
        continue;
    }
    unset($values['files'][$key]);
}
