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

    function __construct($args, $identifyResponse, $callbacks) {

        $this->args = $args;
        $this->identifyResponse = $identifyResponse;
        $this->listMetadataFormatsCallback = $callbacks['ListMetadataFormats'];
        $this->listSetsCallback = $callbacks['ListSets'];
        $this->listRecordsCallback = $callbacks['ListRecords'];
        $this->getRecordCallback = $callbacks['GetRecord'];
        $this->respond();
    }

    private function respond() {
        if (!isset($this->args['verb']) || empty($this->args['verb'])) {
            $this->errors[] = new OAI2Exception('noVerb');
        } else {
            switch ($this->args['verb']) {

                case 'Identify': $this->identify(); break;

                case 'ListMetadataFormats': $this->listMetadataFormats(); break;

                case 'ListSets': $this->listSets(); break;

                case 'ListIdentifiers':
                case 'ListRecords': $this->listRecords(); break;

                case 'GetRecord': $this->getRecord(); break;

                default: $this->errors[] = new OAI2Exception('badVerb', $this->args['verb']);
            }
        }
        if (empty($this->errors)) {

            if (isset($this->outputObj)) {
                header(CONTENT_TYPE);
                $this->outputObj->display();
            } else {
                exit("Nothing to output. May be a bug.");
            }
        } else {
            $this->errorResponse();
        }
    }

    private function errorResponse() {
        $e = new OAI2XMLError($this->args,$this->errors);
        header(CONTENT_TYPE);
        $e->display();
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

        if (count($this->args) > 1) {
            foreach($args as $key => $val) {
                if(strcmp($key,"verb")!=0) {
                    $this->errors[] = new OAI2Exception('badArgument', $key, $val);
                }	
            }
        }

        $this->outputObj = new ANDS_Response_XML($this->args);
        foreach($this->identifyResponse as $key => $val) {
            $this->outputObj->add2_verbNode($key, $val);
        }
    }

    /**
     * Response to Verb ListMetadataFormats
     *
     * The information of supported metadata formats
     */
    public function listMetadataFormats() {

        $checkList = array("ops"=>array("identifier"));
        $this->checkArgs($checkList);

        try {
            if ($formats = call_user_func($this->listMetadataFormatsCallback, $this->args['identifier'])) {
                $this->outputObj = new ANDS_Response_XML($this->args);
                foreach($formats as $key => $val) {
                    $cmf = $this->outputObj->add2_verbNode("metadataFormat");
                    $this->outputObj->addChild($cmf,'metadataPrefix',$key);
                    $this->outputObj->addChild($cmf,'schema',$val['schema']);
                    $this->outputObj->addChild($cmf,'metadataNamespace',$val['metadataNamespace']);
                }
                return $outputObj;
            }
            $this->errors[] = new OAI2Exception('noMetadataFormats'); 
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

        if (isset($this->args['resumptionToken']) && count($this->args) > 2) {
            $this->errors[] = new OAI2Exception('exclusiveArgument');
        }
        $checkList = array("ops"=>array("resumptionToken"));
        $this->checkArgs($checkList);

        if ($sets = call_user_func($this->listSetsCallback)) {

            $this->outputObj = new ANDS_Response_XML($this->args);
            foreach($sets as $set) {

                $setNode = $this->outputObj->add2_verbNode("set");

                foreach($set as $key => $val) {
                    if($key=='setDescription') {
                        $desNode = $this->outputObj->addChild($setNode,$key);
                        $des = $this->outputObj->doc->createDocumentFragment();
                        $des->appendXML($val);
                        $desNode->appendChild($des);
                    } else {
                        $this->outputObj->addChild($setNode,$key,$val);
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

        $checkList = array("required"=>array("metadataPrefix","identifier"));
        $this->checkArgs($checkList);

        $metadataPrefix = $this->args['metadataPrefix'];

        $metadataFormats = call_user_func($this->listMetadataFormatsCallback);

        if (!isset($metadataFormats[$metadataPrefix])) {
            $this->errors[] = new OAI2Exception('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
        }

        try {
            if ($record = call_user_func($this->getRecordCallback, $this->args['identifier'], $metadataPrefix)) {

                $identifier = $record['identifier'];

                $datestamp = formatDatestamp($record['datestamp']); 

                $set = $record['set'];

                $status_deleted = (isset($record['deleted']) && ($record['deleted'] == 'true') && 
                                   (($this->identifyResponse['deletedRecord'] == 'transient') ||
                                    ($this->identifyResponse['deletedRecord'] == 'persistent')));

                $this->outputObj = new ANDS_Response_XML($this->args);
                $cur_record = $this->outputObj->create_record();
                $cur_header = $this->outputObj->create_header($identifier, $datestamp, $set, $cur_record);
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
            if (count($this->args) > 2) {
                $this->errors[] = new OAI2Exception('exclusiveArgument');
            }
            $checkList = array("ops"=>array("resumptionToken"));
        } else {
            $checkList = array("required"=>array("metadataPrefix"),"ops"=>array("from","until","set"));
        }
        $this->checkArgs($checkList);

        $metadataFormats = call_user_func($this->listMetadataFormatsCallback, $this->args);
        if (!isset($metadataFormats[$this->args['metadataPrefix']])) {
            $this->errors[] = new OAI2Exception('cannotDisseminateFormat', 'metadataPrefix', $this->args['metadataPrefix']);
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

        } else {
            $deliveredRecords = 0;
            $metadataPrefix = $this->args['metadataPrefix'];
            $from = isset($this->args['from']) ? $this->args['from'] : '';
            $until = isset($this->args['until']) ? $this->args['until'] : '';
            $set = isset($this->args['set']) ? $this->args['set'] : '';
        }

        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        $maxItems = 1000;
        try {

            $records_count = call_user_func($this->listRecordsCallback, $metadataPrefix, $from, $until, $set, true);

            $records = call_user_func($this->listRecordsCallback, $metadataPrefix, $from, $until, $set, false, $deliveredRecords, $maxItems);

            $this->outputObj = new ANDS_Response_XML($this->args);
            foreach ($records as $record) {

                $identifier = $record['identifier'];
                $datestamp = formatDatestamp($record['datestamp']);
                $setspec = $record[$SQL['set']];

                $status_deleted = (isset($record['deleted']) && ($record['deleted'] === true) &&
                                    (($this->identifyResponse['deletedRecord'] == 'transient') ||
                                     ($this->identifyResponse['deletedRecord'] == 'persistent')));

                if($this->args['verb'] == 'ListRecords') {
                    $cur_record = $this->outputObj->create_record();
                    $cur_header = $this->outputObj->create_header($identifier, $datestamp,$setspec,$cur_record);
                    if (!$status_deleted) {
                        $this->add_metadata($cur_record, $record);
                    }	
                } else { // for ListIdentifiers, only identifiers will be returned.
                    $cur_header = $this->outputObj->create_header($identifier, $datestamp,$setspec);
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
                $this->outputObj->create_resumpToken($restoken,$expirationDatetime,$records_count,$deliveredRecords);
            }

        } catch (OAI2Exception $e) {
            $this->errors[] = $e;
        }
    }

    private function add_metadata($cur_record, $record) {

        $meta_node =  $this->outputObj->addChild($cur_record ,"metadata");

        $schema_node = $this->outputObj->addChild($meta_node, $record['metadata']['container_name']);
        foreach ($record['metadata']['container_attributes'] as $name => $value) {
            $schema_node->setAttribute($name, $value);
        }
        foreach ($record['metadata']['fields'] as $name => $value) {
            $this->outputObj->addChild($schema_node, $name, $value);
        }
    }

    /** Check if provided correct arguments for a request.
     *
     * Only number of parameters is checked.
     * metadataPrefix has to be checked before it is used.
     * set has to be checked before it is used.
     * resumptionToken has to be checked before it is used.
     * from and until can easily checked here because no extra information 
     * is needed.
     */
    private function checkArgs($checkList) {

        $metadataFormats = call_user_func($this->listMetadataFormatsCallback);

        // "verb" has been checked before, no further check is needed
        $verb =  $this->args["verb"];

        $test_args = $this->args;
        unset($test_args["verb"]);

        if(isset($checkList['required'])) {
            for($i = 0; $i < count($checkList["required"]); $i++) {

                if (isset($test_args[$checkList['required'][$i]]) == false) {
                    $this->errors[] = new OAI2Exception('missingArgument', $checkList["required"][$i]);
                } else {
                    // if metadataPrefix is set, it is in required section
                    if(isset($test_args['metadataPrefix'])) {
                        $metadataPrefix = $test_args['metadataPrefix'];
                        if (!isset($metadataFormats[$metadataPrefix])) {
                            $this->errors[] = new OAI2Exception('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
                        }
                    }
                    unset($test_args[$checkList["required"][$i]]);
                }
            }
        }

        // check to see if there is unwanted	
        foreach($test_args as $key => $val) {

            if(!in_array($key, $checkList["ops"])) {
                $this->errors[] = new OAI2Exception('badArgument', $key, $val);
            }
            switch ($key) { 
                case 'from':
                case 'until':
                    if(!checkDateFormat($val)) {
                        $this->errors[] = new OAI2Exception('badGranularity', $key, $val); 
                    }
                    break;

                case 'resumptionToken':
                    // only check for expiration
                    if((int)$val+TOKEN_VALID < time())
                        $this->errors[] = new OAI2Exception('badResumptionToken');
                    break;		
            }
        }

        if (!empty($this->errors)) {
            $this->errorResponse();
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
