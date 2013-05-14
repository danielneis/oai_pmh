<?php

class OAI2ServerTest extends PHPUnit_Framework_TestCase {

    function testIdentify() {
        $verb = 'Identify';
    }

    function testIdentifyIllegalParameter() {
        $verb = 'Identify';
        $args = array('test' => 'test');
    }

    function testListMetadataFormats() {
        $verb = 'ListMetadataFormats';
    }

    function testListMetadataFormatsIdentifier() {
        $verb = 'ListMetadataFormats';
        $args = array('identifier' => 'a.b.c');
    }

    function testListMetadataFormatsIllegalIdentifier() {
        $verb = 'ListMetadataFormats';
        $args = array('identifier' => 'illegalIdentifier');
    }

    function testListSets() {
        $verb = 'ListSets';
    }

    function testListSetsResumptionToken() {
        $verb = 'ListSets';
        $args = array('resumptionToken' => '????');
    }

    function testListIdentifiersMetadataPrefix() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc');
    }

    function testListIdentifiersResumptionToken() {
        $verb = 'ListIdentifiers';
        $args = array('resumptionToken' => '????');
    }

    function testListIdentifiersResumptionTokenMetadataPrefix() {
        $verb = 'ListIdentifiers';
        $args = array('resumptionToken' => '????', 'metadataPrefix' => 'oai_dc');
    }

    function testListIdentifiersMetadataPrefixSet() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc', 'set' => 'someSet');
    }

    function testListIdentifiersMetadataPrefixFromUntil() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }

    function testListIdentifiersMetadataPrefixSetFromUntil() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc',
                      'set' => '????', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }

    function testListIdentifiersMetadataPrefixIllegalSetIllegalFromUntil() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc',
                      'set' => 'really_wrong_set',
                      'from' => 'some_random_from', 'until' => 'some_random_until');
    }

    function testListIdentifiersDifferentGranularity() {
        $verb = 'ListIdentifiers';
        $args = array('resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2000-01-01', 'until' => '2000-01-01T00:00:00Z');
    }

    function testListIdentifiersFromGreaterThanUntil() {
        $verb = 'ListIdentifiers';
        $args = array('resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2013-01-01', 'until' => '2000-01-01T00:00:00Z');
    }
    function testListIdentifiers() {
        $verb = 'ListIdentifiers';
        $args = array();
    }
    function testListIdentifiersIllegalMetadataPrefix() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'illegalPrefix');
    }
    function testListIdentifiersMetadataPrefixMetadataPrefix() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc', 'metadataPrefix' => 'oai_dc');
    }
    function testListIdentifiersIllegalResumptionToken() {
        $verb = 'ListIdentifiers';
        $args = array('resumptionToken' => 'illegalToken');
    }
    function testListIdentifiersMetadataPrefixFrom() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc', 'from' => '2001-01-01T00:00:00Z');
    }
    function testListIdentifiersMetadataPrefixFromYear() {
        $verb = 'ListIdentifiers';
        $args = array('metadataPrefix' => 'oai_dc', 'from' => '2001');
    }

    function testListRecords() {
        $verb = 'ListRecords';
        $args = array();
    }
    function testListRecordsMetadataPrefixFromUntil() {
        $verb = 'ListRecords';
        $args = array('metadataPrefix' => 'oai_dc', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }

    function testListRecordsResumptionToken() {
        $verb = 'ListRecords';
        $args = array('resumptionToken' => '????');
    }

    function testListRecordsMetadataPrefixIllegalSetIllegalFromUntil() {
        $verb = 'ListRecords';
        $args = array('metadataPrefix' => 'oai_dc',
                      'set' => 'illegalSet',
                      'from' => 'some_random_from', 'until' => 'some_random_until');
    }
    function testListRecordsDifferentGranularity() {
        $verb = 'ListRecords';
        $args = array('resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2000-01-01', 'until' => '2000-01-01T00:00:00Z');
    }
    function testListRecordsUntilBeforeEarliestDatestamp() {
        $verb = 'ListRecords';
        $args = array('metadataPrefix' => 'oai_dc', 'until' => '1969-01-01T00:00:00Z');
    }
    function testListRecordsIllegalResumptionToken() {
        $verb = 'ListRecords';
        $args = array('resumptionToken' => 'illegalToken');
    }

    function testGetRecordIdentifier() {
        $verb = 'GetRecord';
        $args = array('identifier' => 'a.b.c');
    }
    function testGetRecordIdentifierMetadataPrefix() {
        $verb = 'GetRecord';
        $args = array('identifier' => 'a.b.c', 'metadataPrefix' => 'oai_dc');
    }
    function testGetRecordIdentifierIllegalMetadataPrefix() {
        $verb = 'GetRecord';
        $args = array('identifier' => 'a.b.c', 'metadataPrefix' => 'illegalPrefix');
    }
    function testGetRecordMetadataPrefix() {
        $verb = 'GetRecord';
        $args = array('metadataPrefix' => 'oai_dc');
    }
    function testGetRecordIllegalIdentifierMetadataPrefix() {
        $verb = 'GetRecord';
        $args = array('identifier' => 'illegalID', 'metadataPrefix' => 'oai_dc');
    }
    function testGetRecordInvalidIdentifierMetadataPrefix() {
        $verb = 'GetRecord';
        $args = array('identifier' => 'invalidID', 'metadataPrefix' => 'oai_dc');
    }

    function testIllegalVerb() {
        $verb = 'IllegalVerb';
        $args = array();
    }
}
