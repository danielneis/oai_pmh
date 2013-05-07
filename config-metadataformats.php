<?php
/**
 * - <b>$METADATAFORMATS</b>: List of supported metadata formats. It is a two-dimensional array with keys.
 * Each supported format is one element of this array at the first dimension.
 * The key is the name of a metadata format.
 * The exact number of items within each format associated array depends on the nature of a metadata format.
 * Most definitions are done here but handlers themselves are defined in separated files because only the names of PHP script are listed here.
 *         - metadataPrefix
 *         - schema
 *         - metadataNamespace
 *         - myhandler
 *         - other optional items: record_prefix, record_namespace and etc.
 */
// define all supported metadata formats, has to be an array
//
// myhandler is the name of the file that handles the request for the
// specific metadata format.
// [record_prefix] describes an optional prefix for the metadata
// [record_namespace] describe the namespace for this prefix


$METADATAFORMATS = array (
    'rif' => array('metadataPrefix'=>'rif',
                   'schema'=>'http://services.ands.org.au/sandbox/orca/schemata/registryObjects.xsd',
                   'metadataNamespace'=>'http://ands.org.au/standards/rif-cs/registryObjects/',
                   'myhandler'=>'record_rif.php'
    ),
    'oai_dc' => array('metadataPrefix'=>'oai_dc',
                      'schema'=>'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                      'metadataNamespace'=>'http://www.openarchives.org/OAI/2.0/oai_dc/',
                      'myhandler'=>'record_dc.php',
                      'record_prefix'=>'dc',
                      'record_namespace' => 'http://purl.org/dc/elements/1.1/'
    )
);

if (!is_array($METADATAFORMATS)) { exit("Configuration of METADATAFORMAT has been wrongly set. Correct your ".__FILE__);}
