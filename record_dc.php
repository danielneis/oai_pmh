<?php
/** \file
 * \brief Definition of Dublin Core handler.
 *
 * It is not working as it does not provide any content to the metadata node. It only included
 * to demonstrate how a new metadata can be supported. For a working
 * example, please see record_rif.php.
 *
 * \sa oaidp-config.php 
	*/

function create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {
	$metadata_node = $outputObj->create_metadata($cur_record);
}
