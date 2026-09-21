<?php
// includes/functions.php

/**
 * Normalize an Indonesian phone number into the format wa.me links require:
 * digits only, no leading zero, prefixed with country code 62.
 *
 * Handles common input variants:
 *   0812xxxxxxx   -> 62812xxxxxxx
 *   62812xxxxxxx  -> 62812xxxxxxx (unchanged)
 *   +62812xxxxxxx -> 62812xxxxxxx
 *   812xxxxxxx    -> 62812xxxxxxx (bare, no prefix)
 */
function normalize_wa_number(string $raw): string
{
    $digits = preg_replace('/[^0-9]/', '', $raw);

    if (substr($digits, 0, 1) === '0') {
        $digits = '62' . substr($digits, 1);
    } elseif (substr($digits, 0, 2) !== '62') {
        $digits = '62' . $digits;
    }

    return $digits;
}
?>
