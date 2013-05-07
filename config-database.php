<?php
//
// DATABASE SETUP
//
$DB_HOST   = '';
$DB_USER   = '';
$DB_PASSWD = '';
$DB_NAME   = '';

/*
 * - <b>$SQL</b>: Settings for database and queries from database
 *
 * - <b>$DSN</b>: DSN for connecting your database. Reference PDO for details.
 *
 */

// Data Source Name: This is the universal connection string
// if you use something other than mysql edit accordingly.
// Example for MySQL
// $DSN = "mysql://$DB_USER:$DB_PASSWD@$DB_HOST/$DB_NAME";
// Example for Oracle
// $DSN = "oci8://$DB_USER:$DB_PASSWD@$DB_NAME";

$DSN = "mysql:host={$DB_HOST};dbname={$DB_NAME};port=3306;";

// the charset you store your metadata in your database
// currently only utf-8 and iso8859-1 are supported
$charset = "utf8";

// if entities such as < > ' " in your metadata has already been escaped
// then set this to true (e.g. you store < as &lt; in your DB)
$xmlescaped = false;

// We store multiple entries for one element in a single row
// in the database. SQL['split'] lists the delimiter for these entries.
// If you do not do this, do not define $SQL['split']
// $SQL['split'] = ';';

// the name of the table where your store your metadata's header
$SQL['table'] = 'oai_headers';

// the name of the column where you store the unique identifiers
// pointing to your item.
// this is your internal identifier for the item
$SQL['identifier'] = 'oai_identifier';

$SQL['metadataPrefix'] = 'oai_metadataprefix';

// If you want to expand the internal identifier in some way
// use this (but not for OAI stuff, see next line)
$idPrefix = '';

// this is your external (OAI) identifier for the item
// this will be expanded to
// oai:$repositoryIdentifier:$idPrefix$SQL['identifier']
// should not be changed
//
// Commented out 24/11/10 14:19:09
// $oaiprefix = "oai".$delimiter.$repositoryIdentifier.$delimiter.$idPrefix;
$oaiprefix = "";

// adjust anIdentifier with sample contents an identifier
// $sampleIdentifier     = $oaiprefix.'anIdentifier';

// the name of the column where you store your datestamps
$SQL['datestamp'] = 'datestamp';

// the name of the column where you store information whether
// a record has been deleted. Leave it as it is if you do not use
// this feature.
$SQL['deleted'] = 'deleted';

// to be able to quickly retrieve the sets to which one item belongs,
// the setnames are stored for each item
// the name of the column where you store sets
$SQL['set'] = 'oai_set';
