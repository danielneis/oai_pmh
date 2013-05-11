<?php
/** \file
 * \brief Definition of Dublin Core handler.
 *
 * It is not working as it does not provide any content to the metadata node. It only included
 * to demonstrate how a new metadata can be supported. For a working
 * example, please see record_rif.php.
 *
 */

function create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {

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
