<?php
/* ===========================================================================
    tx_rnbase_util_SearchBase
=========================================================================== */
if(!defined('SEARCH_FIELD_JOINED')) {
    // Sonderfall Freitextsuche in mehreren Feldern
    define('SEARCH_FIELD_JOINED', 'JOINED');
    // Sonderfall freie Where-Bedingung
    define('SEARCH_FIELD_CUSTOM', 'CUSTOM');

    define('OP_IN', 'IN STR');
    define('OP_NOTIN', 'NOTIN STR');
    // IN für numerische Werte
    define('OP_NOTIN_INT', 'NOT IN');
    define('OP_IN_INT', 'IN');
    define('OP_IN_SQL', 'IN SQL');
    define('OP_NOTIN_SQL', 'NOTIN SQL');
    define('OP_INSET_INT', 'FIND_IN_SET');
    define('OP_LIKE', 'LIKE');
    define('OP_LIKE_CONST', 'OP_LIKE_CONST');
    define('OP_EQ_INT', '=');
    define('OP_NOTEQ', 'OP_NOTEQ');
    define('OP_NOTEQ_INT', '!=');
    define('OP_EQ_NOCASE', 'OP_EQ_NOCASE');
    define('OP_LT_INT', '<');
    define('OP_LTEQ_INT', '<=');
    define('OP_GT_INT', '>');
    define('OP_GTEQ_INT', '>=');
    define('OP_GT', '>STR');
    define('OP_GTEQ', '>=STR');
    define('OP_LT', '<STR');
    define('OP_LTEQ', '<=STR');
    define('OP_EQ', '=STR');
}
