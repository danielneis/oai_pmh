<?php
/**
 * \file
 * \brief Utilities for the OAI Data Provider
 *
 * A collection of functions used.
 */

/** Validates an identifier. The pattern is: '/^[-a-z\.0-9]+$/i' which means 
 * it accepts -, letters and numbers. 
 * Used only by function <B>oai_error</B> code idDoesNotExist. 
 * \param $url Type: string
 */
function is_valid_uri($url) {
    return((bool)preg_match('/^[-a-z\.0-9]+$/i', $url));
}

/** Validates attributes come with the query.
 * It accepts letters, numbers, ':', '_', '.' and -. 
 * Here there are few more match patterns than is_valid_uri(): ':_'.
 * \param $attrb Type: string
 */
function is_valid_attrb($attrb) {
    return preg_match("/^[_a-zA-Z0-9\-\:\.]+$/",$attrb);
}

/** All datestamps used in this system are GMT even
 * return value from database has no TZ information
 */
function formatDatestamp($datestamp) {
    return date("Y-m-d\TH:i:s\Z",strtotime($datestamp));
}

/** The database uses datastamp without time-zone information.
 * It needs to clean all time-zone informaion from time string and reformat it
 */
function checkDateFormat($date) {
    $date = str_replace(array("T","Z")," ",$date);
    $time_val = strtotime($date);
    if(!$time_val) return false;
    if(strstr($date,":")) {
        return date("Y-m-d H:i:s",$time_val);
    } else {
        return date("Y-m-d",$time_val);
    }
}
