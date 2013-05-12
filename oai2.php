<?php
/**
 * OAI Data Provider command processor
 *
 * OAI Data Provider is not designed for human to retrieve data.
 *
 * This is an implementation of OAI Data Provider version 2.0.
 * @see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
 * 
 * It needs other files:
 * - oaidp-config.php : Configuration of provider
 * - oaidp-util.php : Utility functions
 * - xml_creater.php : XML generating functions
 *
 * \todo <b>Remember:</b> to define your own classess for generating metadata records.
 * In common cases, you have to implement your own code to act fully and correctly.
 * For generic usage, you can try the ANDS_Response_XML defined in xml_creater.php.
 */

/**
 * Supported attributes associate to verbs.
 */

if (in_array($_SERVER['REQUEST_METHOD'],array('GET','POST'))) {
    $args = $_REQUEST;
} else {
    $errors[] = oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
}

require_once('oaidp-util.php');

// Always using htmlentities() function to encodes the HTML entities submitted by others.
// No one can be trusted.
foreach ($args as $key => $val) {
    $checking = htmlspecialchars(stripslashes($val));
    if (!is_valid_attrb($checking)) {
        $errors[] = oai_error('badArgument', $checking);
    } else {$args[$key] = $checking; }
}
if (!empty($errors)) {
    oai_exit();
}

$attribs = array ('from', 'identifier', 'metadataPrefix', 'set', 'resumptionToken', 'until');
foreach($attribs as $val) {
    unset($$val);
}

require_once('oaidp-config.php');
require_once('config/metadataformats.php');
require_once('config/sets.php');
require_once('config/database.php');

// For generic usage or just trying:
// require_once('xml_creater.php');
// In common cases, you have to implement your own code to act fully and correctly.
require_once('ands_tpa.php');

// Default, there is no compression supported
$compress = FALSE;
if (isset($compression) && is_array($compression)) {
    if (in_array('gzip', $compression) && ini_get('output_buffering')) {
        $compress = TRUE;
    }
}

require_once('oai2server.php');

/**
 * Identifier settings. It needs to have proper values to reflect the settings of the data provider.
 * Is MUST be declared in this order
 *
 * - $identifyResponse['repositoryName'] : compulsory. A human readable name for the repository;
 * - $identifyResponse['baseURL'] : compulsory. The base URL of the repository;
 * - $identifyResponse['protocolVersion'] : compulsory. The version of the OAI-PMH supported by the repository;
 * - $identifyResponse['earliestDatestamp'] : compulsory. A UTCdatetime that is the guaranteed lower limit of all datestamps recording changes, modifications, or deletions in the repository. A repository must not use datestamps lower than the one specified by the content of the earliestDatestamp element. earliestDatestamp must be expressed at the finest granularity supported by the repository.
 * - $identifyResponse['deletedRecord'] : the manner in which the repository supports the notion of deleted records. Legitimate values are no ; transient ; persistent with meanings defined in the section on deletion.
 * - $identifyResponse['granularity'] : the finest harvesting granularity supported by the repository. The legitimate values are YYYY-MM-DD and YYYY-MM-DDThh:mm:ssZ with meanings as defined in ISO8601.
 *
 */
$identifyResponse = array();
$identifyResponse["repositoryName"] = 'Moodle Neis';
$identifyResponse["baseURL"] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
$identifyResponse["protocolVersion"] = '2.0';
$identifyResponse['adminEmail'] = 'danielneis@gmail.com';
$identifyResponse["earliestDatestamp"] = '2013-01-01T12:00:00Z';
$identifyResponse["deletedRecord"] = 'no'; // How your repository handles deletions
                                           // no:             The repository does not maintain status about deletions.
                                           //                It MUST NOT reveal a deleted status.
                                           // persistent:    The repository persistently keeps track about deletions
                                           //                with no time limit. It MUST consistently reveal the status
                                           //                of a deleted record over time.
                                           // transient:   The repository does not guarantee that a list of deletions is
                                           //                maintained. It MAY reveal a deleted status for records.
$identifyResponse["granularity"] = 'YYYY-MM-DDThh:mm:ssZ';

$repositoryIdentifier = 'dev2.moodle.ufsc.br.';

$oai2 = new OAI2Server($args, $repositoryIdentifier, $identifyResponse);
