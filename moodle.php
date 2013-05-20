<?php

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

// script must be at moodle/local/oai_pmh
require_once('../../config.php');

$adminEmail = generate_email_supportuser();

$identifyResponse = array();
$identifyResponse["repositoryName"] = $SITE->fullname;;
$identifyResponse["baseURL"] = $CFG->wwwroot.'/local/oai_pmh/moodle.php';
$identifyResponse["protocolVersion"] = '2.0';
$identifyResponse['adminEmail'] = $adminEmail->email;
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

if (!isset($uri)) {
    $uri = 'test.oai_pmh';
}
$uri = parse_url($CFG->wwwroot);
$oai2 = new OAI2Server($uri['host'], $_GET, $identifyResponse,
    array(
        'ListMetadataFormats' =>
        function($identifier = '') {
            return array('oai_dc' => array('metadataPrefix'=>'oai_dc',
                                           'schema'=>'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                                           'metadataNamespace'=>'http://www.openarchives.org/OAI/2.0/oai_dc/',
                                           'record_prefix'=>'dc',
                                           'record_namespace' => 'http://purl.org/dc/elements/1.1/'));
        },

        'ListSets' =>
        function($resumptionToken = '') {
            return array (array('setSpec' => 'all', 'setName' => 'All files'));
        },

        'ListRecords' =>
        function($metadataPrefix, $from = '', $until = '', $set = '', $count = false, $deliveredRecords = 0, $maxItems = 0) {
            global $DB;

            if ($metadataPrefix != 'oai_dc') {
                throw new OAI2Exception('noRecordsMatch');
            }

            $files = array();
            $fs = get_file_storage();
            $hashes = $DB->get_records_sql("SELECT DISTINCT pathnamehash as hash FROM files");
            foreach ($hashes as $h) {
                if ($file = $fs->get_file_by_hash($h->hash)) {
                    $files[] = $file;
                }
            }
            if ($count) {
                return sizeof($files);
            }
            if (empty($files)) {
                throw new OAI2Exception('noRecordsMatch');
            }
            $records = array();
            $now = date('Y-m-d-H:s');
            foreach ($files as $f) {
                $filename = $f->get_filename();
                if (!empty($filename) && ($filename != ' ') && $filename != '.') {
                    $records[] = array('identifier' => $f->get_contenthash(),
                                       'datestamp' => $now,
                                       'set' => 'all',
                                       'metadata' => array(
                                            'container_name' => 'oai_dc:dc',
                                            'container_attributes' => array(
                                                'xmlns:oai_dc' => "http://www.openarchives.org/OAI/2.0/oai_dc/",
                                                'xmlns:dc' => "http://purl.org/dc/elements/1.1/",
                                                'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
                                                'xsi:schemaLocation' =>
                                                'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
                                            ),
                                            'fields' => array(
                                                'dc:title' => $f->get_filename(),
                                                'dc:author' => $f->get_author(),
                                            )
                                       ));
                }
            }
            return $records;
        },

        'GetRecord' =>
        function($identifier, $metadataPrefix) {
            global $DB;

            if ($metadataPrefix != 'oai_dc') {
                throw new OAI2Exception('noRecordsMatch');
            }

            $fs = get_file_storage();
            $record = $DB->get_record_sql("SELECT id, pathnamehash FROM files WHERE contenthash = '{$identifier}' ORDER BY id LIMIT 1");
            if (!$file = $fs->get_file_by_hash($record->pathnamehash)) {
                throw new OAI2Exception('idDoesNotExist');
            }
            $now = date('Y-m-d-H:s');
            return array('identifier' => $file->get_contenthash(),
                         'datestamp' => $now,
                         'set' => 'all',
                         'metadata' => array(
                             'container_name' => 'oai_dc:dc',
                             'container_attributes' => array(
                                  'xmlns:oai_dc' => "http://www.openarchives.org/OAI/2.0/oai_dc/",
                                  'xmlns:dc' => "http://purl.org/dc/elements/1.1/",
                                  'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
                                  'xsi:schemaLocation' =>
                                  'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
                              ),
                              'fields' => array(
                                  'dc:title' => $file->get_filename(),
                                  'dc:author' => $file->get_author(),
                              )
                          ));
        },
    )
);

$response = $oai2->response();
$response->formatOutput = true;
$response->preserveWhiteSpace = false;
header('Content-Type: text/xml');
echo $response->saveXML();
