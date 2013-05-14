<?php

class OAI2XML {

    public $doc; // DOMDocument. Handle of current XML Document object

    function __construct($request_args) {

        $this->doc = new DOMDocument("1.0","UTF-8");
        $oai_node = $this->doc->createElement("OAI-PMH");
        $oai_node->setAttribute("xmlns","http://www.openarchives.org/OAI/2.0/");
        $oai_node->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $oai_node->setAttribute("xsi:schemaLocation","http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");
        $this->addChild($oai_node,"responseDate",gmdate("Y-m-d\TH:i:s\Z"));
        $this->doc->appendChild($oai_node);

        $request = $this->addChild($this->doc->documentElement,"request",MY_URI);
        foreach($request_args as $key => $value) {
            $request->setAttribute($key,$value);
        }
    }

    function display() {
        $pr = new DOMDocument();
        $pr->preserveWhiteSpace = false;
        $pr->formatOutput = true;
        $pr->loadXML($this->doc->saveXML());
        echo $pr->saveXML();
    }

    /**
     * Add a child node to a parent node on a XML Doc: a worker function.
     *
     * @param $mom_node
     *   Type: DOMNode. The target node.
     *
     * @param $name
     *   Type: string. The name of child nade is being added
     *
     * @param $value
     *   Type: string. Text for the adding node if it is a text node.
     *
     * @return DOMElement $added_node
     *   The newly created node, can be used for further expansion.
     *   If no further expansion is expected, return value can be igored.
     */

    function addChild($mom_node,$name, $value='') {
        $added_node = $this->doc->createElement($name,$value);
        $added_node = $mom_node->appendChild($added_node);
        return $added_node;
    }
}

class OAI2XMLError extends OAI2XML {

    function __construct($request_args, $errors) {
        parent::__construct($request_args);

        $oai_node = $this->doc->documentElement;
        foreach($errors as $e) {
            $node = $this->addChild($oai_node,"error",$e->getMessage());
            $node->setAttribute("code",$e->getCode());
        }
    }

}
