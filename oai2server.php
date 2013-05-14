<?php

require_once('oai2exception.php');
require_once('oai2xml.php');

/**
 * The content-type the WWW-server delivers back. For debug-puposes, "text/plain"
 * is easier to view. On a production site you should use "text/xml".
 */
define('CONTENT_TYPE', 'Content-Type: text/xml');

/** After 24 hours resumptionTokens become invalid. Unit is second. */
define('TOKEN_VALID',24*3600);

/** Where token is saved and path is included */
define('TOKEN_PREFIX','/tmp/oai_pmh-');

class OAI2Server {

    public $errors = array();
    private $args = array();
    private $verb = '';

    function __construct($uri, $args, $identifyResponse, $callbacks) {

        if (!isset($args['verb']) || empty($args['verb'])) {
            $this->errors[] = new OAI2Exception('noVerb');
            $this->errorResponse();
        }

        $this->verb = $args['verb'];
        unset($args['verb']);
        $this->args = $args;

        $this->uri = $uri;

        $this->identifyResponse = $identifyResponse;

        $this->listMetadataFormatsCallback = $callbacks['ListMetadataFormats'];
        $this->listSetsCallback = $callbacks['ListSets'];
        $this->listRecordsCallback = $callbacks['ListRecords'];
        $this->getRecordCallback = $callbacks['GetRecord'];

        $this->response = new OAI2XMLResponse($this->uri, $this->verb, $this->args);

        $this->respond();
    }

    private function respond() {

        switch ($this->verb) {

            case 'Identify': $this->identify(); break;

            case 'ListMetadataFormats': $this->listMetadataFormats(); break;

            case 'ListSets': $this->listSets(); break;

            case 'ListIdentifiers':
            case 'ListRecords': $this->listRecords(); break;

            case 'GetRecord': $this->getRecord(); break;

            default: $this->errors[] = new OAI2Exception('badVerb', $this->args['verb']);
        }

        if (empty($this->errors)) {
            header(CONTENT_TYPE);
            $this->response->display();
        } else {
            $this->errorResponse();
        }
    }

    private function errorResponse() {
        $errorResponse = new OAI2XMLResponse($this->uri, $this->verb, $this->args);
        $oai_node = $errorResponse->doc->documentElement;
        foreach($this->errors as $e) {
            $node = $errorResponse->addChild($oai_node,"error",$e->getMessage());
            $node->setAttribute("code",$e->getOAI2Code());
        }
        header(CONTENT_TYPE);
        $errorResponse->display();
        exit();
    }

    /**
     * Response to Verb Identify
     *
     * Tell the world what the data provider is. Usually it is static once the provider has been set up.
     *
     * http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm for details
     */
    public function identify() {

        if (count($this->args) > 0) {
            foreach($args as $key => $val) {
                $this->errors[] = new OAI2Exception('badArgument', $key, $val);
            }
            $this->errorResponse();
        }

        foreach($this->identifyResponse as $key => $val) {
            $this->response->addToVerbNode($key, $val);
        }
    }

    /**
     * Response to Verb ListMetadataFormats
     *
     * The information of supported metadata formats
     */
    public function listMetadataFormats() {

        foreach ($this->args as $argument => $value) {
            if ($argument != 'identifier') {
                $this->errors[] = new OAI2Exception('badArgument', $argument, $value);
            }
        }
        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        try {
            if ($formats = call_user_func($this->listMetadataFormatsCallback, $this->args['identifier'])) {
                foreach($formats as $key => $val) {
                    $cmf = $this->response->addToVerbNode("metadataFormat");
                    $this->response->addChild($cmf,'metadataPrefix',$key);
                    $this->response->addChild($cmf,'schema',$val['schema']);
                    $this->response->addChild($cmf,'metadataNamespace',$val['metadataNamespace']);
                }
            } else {
                $this->errors[] = new OAI2Exception('noMetadataFormats'); 
            }
        } catch (OAI2Exception $e) {
            $this->errors[] = $e;
        }
    }

    /**
     * Response to Verb ListSets
     *
     * Lists what sets are available to records in the system.
     * This variable is filled in config-sets.php
     */
    public function listSets() {

        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new OAI2Exception('exclusiveArgument');
            } else {
                if ((int)$val+TOKEN_VALID < time()) {
                    $this->errors[] = new OAI2Exception('badResumptionToken');
                }
            }
        }
        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        if ($sets = call_user_func($this->listSetsCallback)) {

            foreach($sets as $set) {

                $setNode = $this->response->addToVerbNode("set");

                foreach($set as $key => $val) {
                    if($key=='setDescription') {
                        $desNode = $this->response->addChild($setNode,$key);
                        $des = $this->response->doc->createDocumentFragment();
                        $des->appendXML($val);
                        $desNode->appendChild($des);
                    } else {
                        $this->response->addChild($setNode,$key,$val);
                    }
                }
            }
        } else {
            $this->errors[] = new OAI2Exception('noSetHierarchy');
        }
    }

    /**
     * Response to Verb GetRecord
     *
     * Retrieve a record based its identifier.
     *
     * Local variables <B>$metadataPrefix</B> and <B>$identifier</B> need to be provided through global array variable <B>$args</B> 
     * by their indexes 'metadataPrefix' and 'identifier'.
     * The reset of information will be extracted from database based those two parameters.
     */
    public function getRecord() {

        if (!isset($this->args['metadataPrefix'])) {
            $this->errors[] = new OAI2Exception('missingArgument', 'metadataPrefix');
        } else {
            $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
            if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                $this->errors[] = new OAI2Exception('cannotDisseminateFormat', 'metadataPrefix', $this->args['metadataPrefix']);
            }
        }
        if (!isset($this->args['identifier'])) {
            $this->errors[] = new OAI2Exception('missingArgument', 'identifier');
        }
        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        try {
            if ($record = call_user_func($this->getRecordCallback, $this->args['identifier'], $this->args['metadataPrefix'])) {

                $identifier = $record['identifier'];

                $datestamp = formatDatestamp($record['datestamp']); 

                $set = $record['set'];

                $status_deleted = (isset($record['deleted']) && ($record['deleted'] == 'true') && 
                                   (($this->identifyResponse['deletedRecord'] == 'transient') ||
                                    ($this->identifyResponse['deletedRecord'] == 'persistent')));

                $cur_record = $this->response->addToVerbNode('record');
                $cur_header = $this->response->createHeader($identifier, $datestamp, $set, $cur_record);
                if ($status_deleted) {
                    $cur_header->setAttribute("status","deleted");
                } else {
                    $this->add_metadata($cur_record, $record);
                }
            }
        } catch (OAI2Exception $e) {
            $this->errors[] = $e;
        }
    }

    /**
     * Response to Verb ListRecords
     *
     * Lists records according to conditions. If there are too many, a resumptionToken is generated.
     * - If a request comes with a resumptionToken and is still valid, read it and send back records.
     * - Otherwise, set up a query with conditions such as: 'metadataPrefix', 'from', 'until', 'set'.
     * Only 'metadataPrefix' is compulsory.  All conditions are accessible through global array variable <B>$args</B>  by keywords.
     */
    public function listRecords() {

        if (isset($this->args['resumptionToken'])) {
            if (count($this->args) > 1) {
                $this->errors[] = new OAI2Exception('exclusiveArgument');
            } else {
                if ((int)$val+TOKEN_VALID < time()) {
                    $this->errors[] = new OAI2Exception('badResumptionToken');
                }
            }
        } else {
            if (!isset($this->args['metadataPrefix'])) {
                $this->errors[] = new OAI2Exception('missingArgument', 'metadataPrefix');
            } else {
                $metadataFormats = call_user_func($this->listMetadataFormatsCallback);
                if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
                    $this->errors[] = new OAI2Exception('cannotDisseminateFormat', 'metadataPrefix', $this->args['metadataPrefix']);
                }
            }
            if (isset($this->args['from'])) {
                if(!checkDateFormat($this->args['from'])) {
                    $this->errors[] = new OAI2Exception('badGranularity', 'from', $this->args['from']); 
                }
            }
            if (isset($this->args['until'])) {
                if(!checkDateFormat($this->args['until'])) {
                    $this->errors[] = new OAI2Exception('badGranularity', 'until', $this->args['until']); 
                }
            }
        }

        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        // Resume previous session?
        if (isset($this->args['resumptionToken'])) {

            if (!file_exists(TOKEN_PREFIX.$this->args['resumptionToken'])) {
                $this->errors[] = new OAI2Exception('badResumptionToken', '', $this->args['resumptionToken']);
            } else {

                if ($readings = $this->readResumptionToken(TOKEN_PREFIX.$this->args['resumptionToken'])) {
                    list($deliveredRecords, $metadataPrefix, $from, $until, $set) = $readings;
                } else {
                    $this->errors[] = new OAI2Exception('badResumptionToken', '', $this->args['resumptionToken']);
                }

            }

            if (!empty($this->errors)) {
                $this->errorResponse();
            }

        } else {
            $deliveredRecords = 0;
            $metadataPrefix = $this->args['metadataPrefix'];
            $from = isset($this->args['from']) ? $this->args['from'] : '';
            $until = isset($this->args['until']) ? $this->args['until'] : '';
            $set = isset($this->args['set']) ? $this->args['set'] : '';
        }

        $maxItems = 1000;
        try {

            $records_count = call_user_func($this->listRecordsCallback, $metadataPrefix, $from, $until, $set, true);

            $records = call_user_func($this->listRecordsCallback, $metadataPrefix, $from, $until, $set, false, $deliveredRecords, $maxItems);

            foreach ($records as $record) {

                $identifier = $record['identifier'];
                $datestamp = formatDatestamp($record['datestamp']);
                $setspec = $record['set'];

                $status_deleted = (isset($record['deleted']) && ($record['deleted'] === true) &&
                                    (($this->identifyResponse['deletedRecord'] == 'transient') ||
                                     ($this->identifyResponse['deletedRecord'] == 'persistent')));

                if($this->args['verb'] == 'ListRecords') {
                    $cur_record = $this->response->createToVerNode('record');
                    $cur_header = $this->response->createHeader($identifier, $datestamp,$setspec,$cur_record);
                    if (!$status_deleted) {
                        $this->add_metadata($cur_record, $record);
                    }	
                } else { // for ListIdentifiers, only identifiers will be returned.
                    $cur_header = $this->response->createHeader($identifier, $datestamp,$setspec);
                }
                if ($status_deleted) {
                    $cur_header->setAttribute("status","deleted");
                }
            }

            // Will we need a new ResumptionToken?
            if ($records_count - $deliveredRecords > $maxItems) {

                $deliveredRecords +=  $maxItems;
                $restoken = $this->createResumptionToken($deliveredRecords);

                $expirationDatetime = gmstrftime('%Y-%m-%dT%TZ', time()+TOKEN_VALID);	

            } elseif (isset($args['resumptionToken'])) {
                // Last delivery, return empty ResumptionToken
                $restoken = null;
                $expirationDatetime = null;
            }

            if (isset($restoken)) {
                $this->response->createResumptionToken($restoken,$expirationDatetime,$records_count,$deliveredRecords);
            }

        } catch (OAI2Exception $e) {
            $this->errors[] = $e;
        }
    }

    private function add_metadata($cur_record, $record) {

        $meta_node =  $this->response->addChild($cur_record ,"metadata");

        $schema_node = $this->response->addChild($meta_node, $record['metadata']['container_name']);
        foreach ($record['metadata']['container_attributes'] as $name => $value) {
            $schema_node->setAttribute($name, $value);
        }
        foreach ($record['metadata']['fields'] as $name => $value) {
            $this->response->addChild($schema_node, $name, $value);
        }
    }

    private function createResumptionToken($delivered_records) {

        list($usec, $sec) = explode(" ", microtime());
        $token = ((int)($usec*1000) + (int)($sec*1000));

        $fp = fopen (TOKEN_PREFIX.$token, 'w');
        if($fp==false) { 
            exit("Cannot write. Writer permission needs to be changed.");
        }	
        fputs($fp, "$delivered_records#"); 
        fputs($fp, "$metadataPrefix#"); 
        fputs($fp, "{$this->args['from']}#"); 
        fputs($fp, "{$this->args['until']}#"); 
        fputs($fp, "{$this->args['set']}#"); 
        fclose($fp);
        return $token; 
    }

    private function readResumptionToken($resumptionToken) {
        $rtVal = false;
        $fp = fopen($resumptionToken, 'r');
        if ($fp != false) {
            $filetext = fgets($fp, 255);
            $textparts = explode('#', $filetext);
            fclose($fp); 
            unlink($resumptionToken);
            $rtVal = array_values($textparts);
        } 
        return $rtVal; 
    }
}
