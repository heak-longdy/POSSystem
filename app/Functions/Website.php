<?php

function format_number($number)
{

    $number = (0 + str_replace(",", "", $number));
    if (!is_numeric($number)) {
        return false;
    }

    if ($number > 1000000000000) {
        return round(($number / 1000000000000), 2) . 'T';
    } else if ($number > 1000000000) {
        return round(($number / 1000000000), 2) . 'B';
    } else if ($number > 1000000) {
        return round(($number / 1000000), 2) . 'M';
    } else if ($number > 1000) {
        return round(($number / 1000), 2) . 'K';
    }
    return number_format($number);
}

function getMapEmbedSrc($embedMap)
{
    preg_match('~iframe.*src="([^"]*)"~', $embedMap, $result);
    if (!isset($embedMap) || count($result) <= 0) {
        return 'https://www.google.com/maps/embed?pb=';
    }
    return $result[1];
}
