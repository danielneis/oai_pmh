<?php

class OAI2Exception extends Exception {

    function __construct($code = 0, $argument = '', $value = '') {

        $this->errorTable = array(
            'badArgument' => array(
                'text' => "Attribute '{$argument}' is not allowed to appear in element 'request'.",
            ),
            'badResumptionToken' => array(
                'text' => "The resumptionToken '{$value}' does not exist or has already expired.",
            ),
            'badGranularity' => array(
                'text' => "The value '{$value}' of attribute '{$argument}' on element 'request' is not valid with respect to its type, 'UTCdatetimeType'.",
                'code' => 'badArgument',
            ),
            'badVerb' => array(
                'text' => "Illegal OAI verb",
            ),
            'cannotDisseminateFormat' => array(
                'text' => "The metadata format '{$value}' given by {$argument} is not supported by this repository.",
            ),
            'exclusiveArgument' => array(
                'text' => 'The usage of resumptionToken as an argument allows no other arguments.',
                'code' => 'badArgument',
            ),
            'idDoesNotExist' => array(
                'text' => "The value '{$value}' of the identifier does not exist in this repository.",
            ),
            'missingArgument' => array(
                'text' => "The required argument '{$argument}' is missing in the request.",
                'code' => 'badArgument',
            ),
            'noRecordsMatch' => array(
                'text' => 'The combination of the given values results in an empty list.',
            ),
            'noMetadataFormats' => array(
                'text' => 'There are no metadata formats available for the specified item.',
            ),
            'noSetHierarchy' => array(
                'text' => 'This repository does not support sets.',
            ),
            'sameArgument' => array(
                'text' => 'Do not use the same argument more than once.',
                'code' => 'badArgument',
            ),
            'sameVerb' => array(
                'text' => 'Do not use verb more than once.',
                'code' => 'badVerb',
            ),
            'notImp' => array(
                'text' => 'Not yet implemented.',
                'code' => 'debug',
            ),
            ''=> array(
                'text' => "Unknown error: code: '{'code'}', argument: '{$argument}', value: '{$value}'",
                'code' => 'badArgument',
            )
        );
        parent::__construct($this->errorTable[$code]['text']);
        $this->code = $code;
    }

    public function getOAI2Code() {
        return $this->code;
    }
}
