<?php

class OAI2ServerTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->xml = new DomDocument("1.0", "UTF-8");
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
            <responseDate>'.date('Y-m-d\TH:i:s\Z').'</responseDate>
            </OAI-PMH>';
        $this->xml->loadXML($xml);
        $this->uri = 'test.oai_pmh';
    }

    function testIdentify() {
        $xml = '<request verb="Identify">test.oai_pmh</request>
            <Identify>
            <repositoryName>OAI2 PMH Test</repositoryName>
            <baseURL>http://198.199.108.242/~neis/oai_pmh/oai2.php</baseURL>
            <protocolVersion>2.0</protocolVersion>
            <adminEmail>danielneis@gmail.com</adminEmail>
            <earliestDatestamp>2013-01-01T12:00:00Z</earliestDatestamp>
            <deletedRecord>no</deletedRecord>
            <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
            </Identify>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'Identify');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }

    function testIdentifyIllegalParameter() {
        $verb = 'Identify';
        $args = array('test' => 'test');
    }

    function testListMetadataFormats() {
        $xml = '<request verb="ListMetadataFormats">test.oai_pmh</request>
        <ListMetadataFormats>
            <metadataFormat>
            <metadataPrefix>rif</metadataPrefix>
            <schema>http://services.ands.org.au/sandbox/orca/schemata/registryObjects.xsd</schema>
            <metadataNamespace>http://ands.org.au/standards/rif-cs/registryObjects/</metadataNamespace>
            </metadataFormat>
            <metadataFormat>
            <metadataPrefix>oai_dc</metadataPrefix>
            <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
            <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
            </metadataFormat>
            </ListMetadataFormats>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'ListMetadataFormats');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
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
        $xml = '<request verb="ListSets">test.oai_pmh</request>
            <ListSets>
            <set>
            <setSpec>class:collection</setSpec>
            <setName>Collections</setName>
            </set>
            <set>
            <setSpec>math</setSpec>
            <setName>Mathematics</setName>
            </set>
            <set>
            <setSpec>phys</setSpec>
            <setName>Physics</setName>
            </set>
            <set>
            <setSpec>phdthesis</setSpec>
            <setName>PHD Thesis</setName>
            <setDescription>
            <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/  http://www.openarchives.org/OAI/2.0/oai_dc.xsd">  <dc:description>This set contains metadata describing  electronic music recordings made during the 1950ies</dc:description>  </oai_dc:dc>
            </setDescription>
            </set>
            </ListSets>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'ListSets');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }

    function testListSetsResumptionToken() {
        $verb = 'ListSets';
        $args = array('resumptionToken' => '????');
    }

    function testListIdentifiersMetadataPrefix() {
        $xml = '<request verb="ListIdentifiers" metadataPrefix="oai_dc">test.oai_pmh</request>
            <ListIdentifiers>
            <header>
            <identifier>dev.testing.pmh</identifier>
            <datestamp>2013-05-14T18:41:00Z</datestamp>
            <setSpec>class:activity</setSpec>
            </header>
            </ListIdentifiers>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }

    function testListIdentifiers() {
        $args = array('verb' => 'ListIdentifiers');
    }
    function testListIdentifiersResumptionToken() {
        $args = array('verb' => 'ListIdentifiers', 'resumptionToken' => '????');
    }
    function testListIdentifiersResumptionTokenMetadataPrefix() {
        $args = array('verb' => 'ListIdentifiers', 'resumptionToken' => '????', 'metadataPrefix' => 'oai_dc');
    }
    function testListIdentifiersMetadataPrefixSet() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'set' => 'someSet');
    }
    function testListIdentifiersMetadataPrefixFromUntil() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }
    function testListIdentifiersMetadataPrefixSetFromUntil() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc',
                      'set' => '????', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }
    function testListIdentifiersMetadataPrefixIllegalSetIllegalFromUntil() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc',
                      'set' => 'really_wrong_set',
                      'from' => 'some_random_from', 'until' => 'some_random_until');
    }
    function testListIdentifiersDifferentGranularity() {
        $args = array('verb' => 'ListIdentifiers', 'resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2000-01-01', 'until' => '2000-01-01T00:00:00Z');
    }
    function testListIdentifiersFromGreaterThanUntil() {
        $args = array('verb' => 'ListIdentifiers', 'resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2013-01-01', 'until' => '2000-01-01T00:00:00Z');
    }
    function testListIdentifiersIllegalMetadataPrefix() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'illegalPrefix');
    }
    function testListIdentifiersMetadataPrefixMetadataPrefix() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'metadataPrefix' => 'oai_dc');
    }
    function testListIdentifiersIllegalResumptionToken() {
        $args = array('verb' => 'ListIdentifiers', 'resumptionToken' => 'illegalToken');
    }
    function testListIdentifiersMetadataPrefixFrom() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'from' => '2001-01-01T00:00:00Z');
    }
    function testListIdentifiersMetadataPrefixFromYear() {
        $args = array('verb' => 'ListIdentifiers', 'metadataPrefix' => 'oai_dc', 'from' => '2001');
    }

    function testListRecords() {
        $verb = 'ListRecords';
        $args = array('verb' => 'ListRecords');
    }
    function testListRecordsMetadataPrefix() {

        $xml = '<request verb="ListRecords" metadataPrefix="oai_dc">test.oai_pmh</request>
                <ListRecords>
                <record>
                <header>
                <identifier>dev.testing.pmh</identifier>
                <datestamp>2013-05-14T18:11:00Z</datestamp>
                <setSpec>class:activity</setSpec>
                </header>
                <metadata>
                <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                <dc:title>Testing records</dc:title>
                <dc:author>Neis</dc:author>
                </oai_dc:dc>
                </metadata>
                </record>
                </ListRecords>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }
    function testListRecordsMetadataPrefixFromUntil() {
        $args = array('verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc', 'from' => '2000-01-01', 'until' => '2000-01-01');
    }
    function testListRecordsResumptionToken() {
        $args = array('verb' => 'ListRecords', 'resumptionToken' => '????');
    }
    function testListRecordsMetadataPrefixIllegalSetIllegalFromUntil() {
        $args = array('verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc',
                      'set' => 'illegalSet',
                      'from' => 'some_random_from', 'until' => 'some_random_until');
    }
    function testListRecordsDifferentGranularity() {
        $args = array('verb' => 'ListRecords', 'resumptionToken' => '????', 'metadataPrefix' => 'oai_dc',
                      'from' => '2000-01-01', 'until' => '2000-01-01T00:00:00Z');
    }
    function testListRecordsUntilBeforeEarliestDatestamp() {
        $args = array('verb' => 'ListRecords', 'metadataPrefix' => 'oai_dc', 'until' => '1969-01-01T00:00:00Z');
    }
    function testListRecordsIllegalResumptionToken() {
        $args = array('verb' => 'ListRecords', 'resumptionToken' => 'illegalToken');
    }

    function testGetRecord() {
        $args = array('verb' => 'GetRecord');
    }
    function testGetRecordIdentifier() {
        $args = array('verb' => 'GetRecord', 'identifier' => 'a.b.c');
    }
    function testGetRecordIdentifierMetadataPrefix() {

        $xml = '<request verb="GetRecord" metadataPrefix="oai_dc" identifier="a.b.c">test.oai_pmh</request>
                <GetRecord>
                <record>
                <header>
                <identifier>a.b.c</identifier>
                <datestamp>2013-05-14T18:08:00Z</datestamp>
                <setSpec>class:activity</setSpec>
                </header>
                <metadata>
                <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                <dc:title>Testing records</dc:title>
                <dc:author>Neis</dc:author>
                </oai_dc:dc>
                </metadata>
                </record>
                </GetRecord>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;
        $args = array('verb' => 'GetRecord', 'identifier' => 'a.b.c', 'metadataPrefix' => 'oai_dc');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }
    function testGetRecordIdentifierIllegalMetadataPrefix() {
        $args = array('verb' => 'GetRecord', 'identifier' => 'a.b.c', 'metadataPrefix' => 'illegalPrefix');
    }
    function testGetRecordMetadataPrefix() {
        $args = array('verb' => 'GetRecord', 'metadataPrefix' => 'oai_dc');
    }
    function testGetRecordIllegalIdentifierMetadataPrefix() {
        $args = array('verb' => 'GetRecord', 'identifier' => 'illegalID', 'metadataPrefix' => 'oai_dc');
    }
    function testGetRecordInvalidIdentifierMetadataPrefix() {
        $args = array('verb' => 'GetRecord', 'identifier' => 'invalidID', 'metadataPrefix' => 'oai_dc');
    }

    function testIllegalVerb() {
        $xml = '<request>test.oai_pmh</request>
          <error code="badVerb">Illegal OAI verb</error>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;

        $args = array('verb' => 'IllegalVerb');

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }

    function testNoVerb() {

        $xml = '<request>test.oai_pmh</request>
          <error code="badVerb">Illegal OAI verb</error>';

        $f = $this->xml->createDocumentFragment();
        $f->appendXML($xml);
        $this->xml->documentElement->appendChild($f);

        $return = true;
        $uri = $this->uri;

        $args = array();

        $response = require('oai2.php');

        $this->assertEqualXMLStructure($this->xml->firstChild, $response->firstChild);
    }
}
