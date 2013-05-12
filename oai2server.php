<?php
/* 
 * - Actions:
 * 	- identify.php : About the provider
 * 	- listmetadataformats.php : List supported metadata formats
 * 	- listrecords.php : List identifiers and records
 * 	- listsets.php : List sets
 * 	- getrecord.php : Get a record / Your own implementation for providing metadata records.
 */
class OAI2Server {

    public $errors = array();

    /* 
     * @param $delimiter    = ':';  should not be changed. Only useful when NODE description is included in the response to Identifier
     */
    function __construct($args, $repositoryIdentifier, $identifyResponse,
                         $delimiter = ':', $sampleIdentifier = false, $show_identifier = false) {
        $this->args = $args;
        $this->repositoryIdentier = $repositoryIdentifier;
        $this->identifyResponse = $identifyResponse;
        $this->respond();
    }

    private function respond() {
        if (!isset($this->args['verb']) || empty($this->args['verb'])) {
            $this->errors[] = oai_error('noVerb');
        } else {
            switch ($this->args['verb']) {

                case 'Identify':
                    // we never use compression in Identify
                    $compress = FALSE;
                    $this->outputObj = $this->identify();
                    break;

                case 'ListMetadataFormats':
                    $this->outputObj = $this->listMetadataFormats();
                    break;

                case 'ListSets':
                    $this->outputObj = $this->listSets();
                    break;

                case 'ListIdentifiers':
                case 'ListRecords':
                    $this->outputObj = $this->listRecords();
                    break;

                case 'GetRecord':
                    $this->outputObj = $this->getRecord();
                    break;

                default:
                    // we never use compression with errors
                    $compress = FALSE;
                    $this->errors[] = oai_error('badVerb', $this->args['verb']);
            }
        }
        if (empty($this->errors)) {
            $this->display();
        } else {
            $this->errorResponse();
        }
    }

    function errorResponse() {
        $e = new ANDS_Error_XML($this->args,$this->errors);
        header(CONTENT_TYPE);
        $e->display();
        exit();
    }

    function display() {
        if (isset($this->outputObj)) {

            if ($compress) {
                ob_start('ob_gzhandler');
            }

            header(CONTENT_TYPE);
            $this->outputObj->display();

            if ($compress) {
                ob_end_flush();
            }

        } else {
            exit("Nothing to output. May be a bug.");
        }
    }

    /**
     * Response to Verb Identify
     *
     * Tell the world what the data provider is. Usually it is static once the provider has been set up.
     *
     * http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm for details
     */
    public function identify($show_identifier, $repositoryIdentifier, $delimiter, $sampleIdentifier) {

        if (count($this->args) > 1) {
            foreach($args as $key => $val) {
                if(strcmp($key,"verb")!=0) {
                    $this->errors[] = oai_error('badArgument', $key, $val);
                }	
            }
        }

        $outputObj = new ANDS_Response_XML($this->args);
        foreach($this->identifyResponse as $key => $val) {
            $outputObj->add2_verbNode($key, $val);
        }

        // A description MAY be included.
        // Use this if you choose to comply with a specific format of unique identifiers
        // for items. 
        // See http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm 
        // for details

        // As they will not be changed, using string for simplicity.
        $output = '';
        if ($this->show_identifier && $this->repositoryIdentifier && $this->delimiter && $this->sampleIdentifier) {
                $output .= 
        '  <description>
           <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier
                           http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
            <scheme>oai</scheme>
            <repositoryIdentifier>'.$repositoryIdentifier.'</repositoryIdentifier>
            <delimiter>'.$delimiter.'</delimiter>
            <sampleIdentifier>'.$sampleIdentifier.'</sampleIdentifier>
           </oai-identifier>
          </description>'."\n"; 
        }

        // A description MAY be included.
        // This example from arXiv.org is used by the e-prints community, please adjust
        // see http://www.openarchives.org/OAI/2.0/guidelines-eprints.htm for details

        // To include, change 'false' to 'true'.
        if (false) {
                $output .= 
        '  <description>
           <eprints xmlns="http://www.openarchives.org/OAI/1.1/eprints"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints 
                    http://www.openarchives.org/OAI/1.1/eprints.xsd">
            <content>
             <text>Author self-archived e-prints</text>
            </content>
            <metadataPolicy />
            <dataPolicy />
            <submissionPolicy />
           </eprints>
          </description>'."\n"; 
        }

        // If you want to point harvesters to other repositories, you can list their
        // base URLs. Usage of friends container is RECOMMENDED.
        // see http://www.openarchives.org/OAI/2.0/guidelines-friends.htm 
        // for details

        // To include, change 'false' to 'true'.
        if (false) {
                $output .= 
        '  <description>
           <friends xmlns="http://www.openarchives.org/OAI/2.0/friends/" 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/friends/
                    http://www.openarchives.org/OAI/2.0/friends.xsd">
            <baseURL>http://naca.larc.nasa.gov/oai2.0/</baseURL>
            <baseURL>http://techreports.larc.nasa.gov/ltrs/oai2.0/</baseURL>
            <baseURL>http://physnet.uni-oldenburg.de/oai/oai2.php</baseURL>
            <baseURL>http://cogprints.soton.ac.uk/perl/oai</baseURL>
            <baseURL>http://ub.uni-duisburg.de:8080/cgi-oai/oai.pl</baseURL>
            <baseURL>http://rocky.dlib.vt.edu/~jcdlpix/cgi-bin/OAI1.1/jcdlpix.pl</baseURL>
           </friends>
          </description>'."\n"; 
        }

        // If you want to provide branding information, adjust accordingly.
        // Usage of friends container is OPTIONAL.
        // see http://www.openarchives.org/OAI/2.0/guidelines-branding.htm 
        // for details

        // To include, change 'false' to 'true'.
        if (false) {
                $output .= 
        '  <description>
           <branding xmlns="http://www.openarchives.org/OAI/2.0/branding/"
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/branding/
                     http://www.openarchives.org/OAI/2.0/branding.xsd">
            <collectionIcon>
             <url>http://my.site/icon.png</url>
             <link>http://my.site/homepage.html</link>
             <title>MySite(tm)</title>
             <width>88</width>
             <height>31</height>
            </collectionIcon>
            <metadataRendering 
             metadataNamespace="http://www.openarchives.org/OAI/2.0/oai_dc/" 
             mimeType="text/xsl">http://some.where/DCrender.xsl</metadataRendering>
            <metadataRendering
             metadataNamespace="http://another.place/MARC" 
             mimeType="text/css">http://another.place/MARCrender.css</metadataRendering>
           </branding>
          </description>'."\n";
        }

        if(strlen($output)>10) {
            $des = $outputObj->doc->createDocumentFragment();
            $des->appendXML($output);
            $outputObj->verbNode->appendChild($des);
        }

        return $outputObj;
    }

    /**
     * Response to Verb ListMetadataFormats
     *
     * The information of supported metadata formats :
     * try database table $SQL['table']
     * else try $METADATAFORMATS array from config-metadataformats.php
     */
    public function listMetadataFormats() {
        global $DSN, $DB_USER, $DB_PASSWD, $METADATAFORMATS, $SQL;

        $checkList = array("ops"=>array("identifier"));
        $this->checkArgs($checkList);

        // Create a PDO object
        try {
            $db = new PDO($DSN, $DB_USER, $DB_PASSWD);
        } catch (PDOException $e) {
            exit('Connection failed: ' . $e->getMessage());
        }

        if (isset($this->args['identifier'])) {

            $identifier = $this->args['identifier'];
            $query = 'select '.$SQL['metadataPrefix'].' FROM '.$SQL['table']. " WHERE ".$SQL['identifier']." = '".$id."'";
            $res = $db->query($query);

            if ($res==false) {
                if (SHOW_QUERY_ERROR) {
                    echo __FILE__.','.__LINE__."<br />";
                    echo "Query: $query<br />\n";
                    die($db->errorInfo());
                } else {
                    $this->errors[] = oai_error('idDoesNotExist','', $identifier);
                }
            } else {
                $record = $res->fetch();
                if($record===false) {
                    $this->errors[] = oai_error('idDoesNotExist', '', $identifier);
                } else {
                    $mf = explode(",",$record[$SQL['metadataPrefix']]);    
                }
            }
        }

        //break and clean up on error
        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        $outputObj = new ANDS_Response_XML($this->args);
        if (isset($mf)) {
            foreach($mf as $key) {
                $val = $METADATAFORMATS[$key];
                $this->addMetedataFormat($outputObj,$key, $val);
            }
        } elseif (is_array($METADATAFORMATS)) {
            foreach($METADATAFORMATS as $key=>$val) {
                $this->addMetedataFormat($outputObj,$key, $val);
            }
        } else { // a very unlikely event
            $this->errors[] = oai_error('noMetadataFormats'); 
            $this->errorResponse();
        }

        return $outputObj;
    }

    /**
     * Response to Verb ListSets
     *
     * Lists what sets are available to records in the system.
     * This variable is filled in config-sets.php
     */
    public function listSets() {
        global $SETS;

        $sets = $SETS;

        if (isset($this->args['resumptionToken']) && count($this->args) > 2) {
            $this->errors[] = oai_error('exclusiveArgument');
        }
        $checkList = array("ops"=>array("resumptionToken"));
        $this->checkArgs($checkList);

        if (is_array($sets)) {
            $outputObj = new ANDS_Response_XML($this->args);
            foreach($sets as $set) {
                $setNode = $outputObj->add2_verbNode("set");
                foreach($set as $key => $val) {
                    if($key=='setDescription') {
                        $desNode = $outputObj->addChild($setNode,$key);
                        $des = $outputObj->doc->createDocumentFragment();
                        $des->appendXML($val);
                        $desNode->appendChild($des);
                    } else {
                        $outputObj->addChild($setNode,$key,$val);
                    }
                }
            }
        } else {
            $this->errors[] = oai_error('noSetHierarchy');
            oai_exit();
        }
        return $outputObj;
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
        global $METADATAFORMATS, $DSN, $DB_USER, $DB_PASSWD, $SQL;

        $checkList = array("required"=>array("metadataPrefix","identifier"));
        $this->checkArgs($checkList);

        $metadataPrefix = $this->args['metadataPrefix'];

        if (!isset($METADATAFORMATS[$metadataPrefix])) {
            $this->errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
        }

        // Create a PDO object
        try {
            $db = new PDO($DSN, $DB_USER, $DB_PASSWD);
        } catch (PDOException $e) {
            exit('Connection failed: ' . $e->getMessage());
        }

        $identifier = $this->args['identifier'];
        $query = selectallQuery($metadataPrefix, $identifier);

        $res = $db->query($query);

        if ($res===false) {
            $this->errors[] = oai_error('idDoesNotExist', '', $identifier); 
        } elseif (!$res->rowCount()) { // based on PHP manual, it might only work for some DBs
            $this->errors[] = oai_error('idDoesNotExist', '', $identifier); 
        }

        if (!empty($this->errors)) {
            oai_exit();
        }

        $record = $res->fetch(PDO::FETCH_ASSOC);
        if ($record===false) {
            $this->errors[] = oai_error('idDoesNotExist', '', $identifier);	
        }

        $identifier = $record[$SQL['identifier']];;

        $datestamp = formatDatestamp($record[$SQL['datestamp']]); 

        $status_deleted = (isset($record[$SQL['deleted']]) && ($record[$SQL['deleted']] == 'true') && 
                           ($this->identifyResponse['deletedRecord'] == 'transient' || $this->identifyResponse['deletedRecord'] == 'persistent'));

        $outputObj = new ANDS_Response_XML($this->args);
        $cur_record = $outputObj->create_record();
        $cur_header = $outputObj->create_header($identifier, $datestamp,$record[$SQL['set']],$cur_record);
        // return the metadata record itself
        if ($status_deleted) {
            $cur_header->setAttribute("status","deleted");
        } else {
            call_user_func(array($this, "{$metadataPrefix}_create_metadata"),
                           $outputObj, $cur_record, $identifier, $record[$SQL['set']], $db);
        }
        return $outputObj;
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
        global $SQL, $METADATAFORMATS, $DSN, $DB_USER, $DB_PASSWD, $SETS;

        $sets = $SETS;

        if(isset($this->args['resumptionToken'])) {
            if (count($this->args) > 2) {
                $this->errors[] = oai_error('exclusiveArgument');
            }
            $checkList = array("ops"=>array("resumptionToken"));
        } else {
            $checkList = array("required"=>array("metadataPrefix"),"ops"=>array("from","until","set"));
        }
        $this->checkArgs($checkList);

        // Resume previous session?
        if (isset($this->args['resumptionToken'])) {
            if (!file_exists(TOKEN_PREFIX.$this->args['resumptionToken'])) {
                $this->errors[] = oai_error('badResumptionToken', '', $this->args['resumptionToken']);
            } else {
                $readings = readResumToken(TOKEN_PREFIX.$this->args['resumptionToken']);
                if ($readings == false) {
                    $this->errors[] = oai_error('badResumptionToken', '', $this->args['resumptionToken']);
                } else {
                    list($deliveredrecords, $extquery, $metadataPrefix) = $readings;
                }
            }
        } else { // no, we start a new session
            $deliveredrecords = 0;
            $extquery = '';

            $metadataPrefix = $this->args['metadataPrefix'];

            if (isset($args['from'])) {
                $from = checkDateFormat($this->args['from']);
                $extquery .= fromQuery($from);
            }

            if (isset($args['until'])) {
                $until = checkDateFormat($this->args['until']);
                $extquery .= untilQuery($until);
            }

            if (isset($args['set'])) {
                if (is_array($sets)) {
                    $extquery .= setQuery($this->args['set']);
                } else {
                    $this->errors[] = oai_error('noSetHierarchy');
                }
            }
        }

        if (!isset($METADATAFORMATS[$metadataPrefix])) {
            $this->errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
        }

        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        // Create a PDO object
        try {
            $db = new PDO($DSN, $DB_USER, $DB_PASSWD);
        } catch (PDOException $e) {
            exit('Connection failed: ' . $e->getMessage());
        }

        $query = selectallQuery($metadataPrefix) . $extquery;

        $res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $r = $res->execute();
        if ($r===false) {
            $this->errors[] = oai_error('noRecordsMatch');
        } else {
            $r = $res->setFetchMode(PDO::FETCH_ASSOC);
            if ($r===false) {
                exit("FetchMode is not supported");
            }
            $num_rows = rowCount($metadataPrefix, $extquery, $db);
            if ($num_rows==0) {
                $this->errors[] = oai_error('noRecordsMatch');
            }
        }

        if (!empty($this->errors)) {
            $this->errorResponse();
        }

        // Will we need a new ResumptionToken?
        if($this->args['verb']=='ListRecords') {
            $maxItems = MAXRECORDS;
        } elseif($this->args['verb']=='ListIdentifiers') {
            $maxItems = MAXIDS;
        } else {
            exit("Check ".__FILE__." ".__LINE__.", there is something wrong.");
        }
        $maxrec = min($num_rows - $deliveredrecords, $maxItems);

        if ($num_rows - $deliveredrecords > $maxItems) {
            $cursor = (int)$deliveredrecords + $maxItems;
            $restoken = createResumToken($cursor, $extquery, $metadataPrefix);
            $expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+TOKEN_VALID);	
        } elseif (isset($args['resumptionToken'])) {
            // Last delivery, return empty ResumptionToken
            $restoken = $args['resumptionToken']; // just used as an indicator
            unset($expirationdatetime);
        }

        if (isset($this->args['resumptionToken'])) {
            $record = $res->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $deliveredrecords);
        }
        // Record counter
        $countrec  = 0;

        // Publish a batch to $maxrec number of records
        $outputObj = new ANDS_Response_XML($this->args);
        while ($countrec++ < $maxrec) {
            $record = $res->fetch(PDO::FETCH_ASSOC);
            if ($record===false) {
                if (SHOW_QUERY_ERROR) {
                    echo __FILE__.",". __LINE__."<br />";
                    print_r($db->errorInfo());
                    exit();
                }
            }

            $identifier = $oaiprefix.$record[$SQL['identifier']];
            $datestamp = formatDatestamp($record[$SQL['datestamp']]);
            $setspec = $record[$SQL['set']];

            $status_deleted = (isset($record[$SQL['deleted']]) && ($record[$SQL['deleted']] === true) &&
                    ($this->identifyResponse['deletedRecord'] == 'transient' || $this->identifyResponse['deletedRecord'] == 'persistent'));

            if($this->args['verb']=='ListRecords') {
                $cur_record = $outputObj->create_record();
                $cur_header = $outputObj->create_header($identifier, $datestamp,$setspec,$cur_record);
                // return the metadata record itself
                if (!$status_deleted) {
                    call_user_func(array($this, "{$metadataPrefix}_create_metadata"),
                                   $outputObj, $cur_record, $identifier, $setspec, $db);
                }	
            } else { // for ListIdentifiers, only identifiers will be returned.
                $cur_header = $outputObj->create_header($identifier, $datestamp,$setspec);
            }
            if ($status_deleted) {
                $cur_header->setAttribute("status","deleted");
            }
        }

        // ResumptionToken
        if (isset($restoken)) {
            if(isset($expirationdatetime)) {
                $outputObj->create_resumpToken($restoken,$expirationdatetime,$num_rows,$cursor);
            } else {
                $outputObj->create_resumpToken('',null,$num_rows,$deliveredrecords);
            }	
        }
        return $outputObj;
    }

    /**
     * Add a metadata format node to an ANDS_Response_XML
     * \param &$outputObj
     *	type: ANDS_Response_XML. The ANDS_Response_XML object for output.
     * \param $key
     * 	type string. The name of new node.
     * \param $val
     * 	type: array. Values accessable through keywords 'schema' and 'metadataNamespace'.
     *
     */
    private function addMetedataFormat(&$outputObj,$key,$val) {
        $cmf = $outputObj->add2_verbNode("metadataFormat");
        $outputObj->addChild($cmf,'metadataPrefix',$key);
        $outputObj->addChild($cmf,'schema',$val['schema']);
        $outputObj->addChild($cmf,'metadataNamespace',$val['metadataNamespace']);
    }

    private function rif_create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {

        $metadata_node = $outputObj->create_metadata($cur_record);
        $obj_node = new ANDS_TPA($outputObj, $metadata_node, $db);
        try {
            $obj_node->create_obj_node($setspec, $identifier);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), " when adding $identifier\n";
        }
    }

    private function oai_dc_create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {

        $sql = "SELECT dc_title, dc_creator, dc_subject, dc_description, dc_contributor, dc_publisher,
                       dc_date , dc_type , dc_format , dc_identifier , dc_source , dc_language,
                       dc_relation , dc_coverage , dc_rights 
                  FROM oai_records
                 WHERE oai_set = '{$setspec}'
                   AND oai_identifier = '{$identifier}'";

        $res = exec_pdo_query($db,$sql);
        $record = $res->fetch(PDO::FETCH_ASSOC);

        $meta_node =  $outputObj->addChild($cur_record ,"metadata");

        $schema_node = $outputObj->addChild($meta_node, 'oai_dc:dc');
        $schema_node->setAttribute('xmlns:oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
        $schema_node->setAttribute('xmlns:dc',"http://purl.org/dc/elements/1.1/");
        $schema_node->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
        $schema_node->setAttribute('xsi:schemaLocation',
                                   'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
        foreach ($record as $r => $v) {
            if (!empty($v)) {
                $outputObj->addChild($schema_node, str_replace('_', ':', $r), $v);
            }
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
        global $METADATAFORMATS;

        // "verb" has been checked before, no further check is needed
        $verb =  $this->args["verb"];

        $test_args = $this->args;
        unset($test_args["verb"]);

        if(isset($checkList['required'])) {
            for($i = 0; $i < count($checkList["required"]); $i++) {

                if(isset($test_args[$checkList['required'][$i]])==false) {
                    $this->errors[] = oai_error('missingArgument', $checkList["required"][$i]);
                } else {
                    // if metadataPrefix is set, it is in required section
                    if(isset($test_args['metadataPrefix'])) {
                        $metadataPrefix = $test_args['metadataPrefix'];
                        // Check if the format is supported, it has enough infor (an array), last if a handle has been defined.
                        if (!array_key_exists($metadataPrefix, $METADATAFORMATS) ||
                                !(is_array($METADATAFORMATS[$metadataPrefix]) ||
                                    !isset($METADATAFORMATS[$metadataPrefix]['myhandler']))) {
                            $this->errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
                        }
                    }
                    unset($test_args[$checkList["required"][$i]]);
                }
            }
        }

        if (!empty($this->errors)) return;

        // check to see if there is unwanted	
        foreach($test_args as $key => $val) {

            if(!in_array($key, $checkList["ops"])) {
                $this->errors[] = oai_error('badArgument', $key, $val);
            }
            switch ($key) { 
                case 'from':
                case 'until':
                    if(!checkDateFormat($val)) {
                        $this->errors[] = oai_error('badGranularity', $key, $val); 
                    }
                    break;

                case 'resumptionToken':
                    // only check for expairation
                    if((int)$val+TOKEN_VALID < time())
                        $this->errors[] = oai_error('badResumptionToken');
                    break;		
            }
        }
    }
}
