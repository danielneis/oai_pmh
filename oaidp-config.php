<?php
/**
 * brief Configuration file of current data provider.
 *
 * This is the configuration file for the PHP OAI Data Provider.
 * The response may may be compressed for better performace:
 * - Compression : a compression encoding supported by the repository. The recommended values are those defined for the Content-Encoding header in Section 14.11 of RFC 2616 describing HTTP 1.1. A compression element should not be included for the identity encoding, which is implied.
 * The rest of settings will not normally need to be adjusted. Read source code for details.
 */

/**
 * Whether to show error message for dubug.
 * For installation, testing and debuging set SHOW_QUERY_ERROR to TRUE
 * If set to TRUE, application will die and display query and database error message
 * as soon as there is a problem. Do not set this to TRUE on a production site,
 * since it will show error messages to everybody.
 * If set FALSE, will create XML-output, no matter what happens.
 */
// If everything is running ok, you should use this
define('SHOW_QUERY_ERROR',FALSE);

/**
 * \property CONTENT_TYPE
 * The content-type the WWW-server delivers back. For debug-puposes, "text/plain"
 * is easier to view. On a production site you should use "text/xml".
 */
#define('CONTENT_TYPE','Content-Type: text/plain');
// If everything is running ok, you should use this
define('CONTENT_TYPE', 'Content-Type: text/xml');

// For ANDS to harvest of RIF-CS, originatingSource is plantaccelerator.org.au
// $dataSource = "plantaccelerator.org.au";
define('DATASOURCE','dev2.moodle.ufsc.br');

/** Compression methods supported. Optional (multiple). Default: null.
 *
 * Currently only gzip is supported (you need output buffering turned on,
 * and php compiled with libgz).
 * The client MUST send "Accept-Encoding: gzip" to actually receive
 */
// $compression        = array('gzip');
$compression = null;

// MUST (only one)
// You may choose any name, but for repositories to comply with the oai
// format it has to be unique identifiers for items records.
// see: http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
// Basically use domainname
// please adjust

// For RIF-CS, especially with ANDS, each registryObject much has a group for the ownership of data.
// For detail please see ANDS guide on its web site. Each data provider should have only one REG_OBJ_GROUP
// for this purpose.
define('REG_OBJ_GROUP','Something agreed on');

/** Maximum mumber of the records to deliver
 * (verb is ListRecords)
 * If there are more records to deliver
 * a ResumptionToken will be generated.
 */
define('MAXRECORDS',10);

/** Maximum mumber of identifiers to deliver
 * (verb is ListIdentifiers)
 * If there are more identifiers to deliver
 * a ResumptionToken will be generated.
 */
define('MAXIDS',40);

/** After 24 hours resumptionTokens become invalid. Unit is second. */
define('TOKEN_VALID',24*3600);
$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+TOKEN_VALID);
/** Where token is saved and path is included */
define('TOKEN_PREFIX','/tmp/ANDS_DBPD-');

// The shorthand of xml schema namespace, no need to change this
define('XMLSCHEMA', 'http://www.w3.org/2001/XMLSchema-instance');
