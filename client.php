<?php

require_once('oai2client.php');

$client = new OAI2Client('http://localhost/~daniel/2moodle/local/oai_pmh/moodle.php');

var_dump($client->Identify());
var_dump($client->ListMetadataFormats());
var_dump($client->ListSets());
var_dump($client->ListIdentifiers(array('metadataPrefix' => 'oai_dc')));
var_dump($client->ListRecords(array('metadataPrefix' => 'oai_dc')));
var_dump($client->GetRecord('aaaaa', 'oai_dc'));
