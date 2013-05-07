#
# Table structure for table 'oai_records'
#
# This is different to the structure for PostgreSQL to show many structures can be choosen.
#
#
CREATE TABLE oai_records (
  serial int(11) DEFAULT '0' NOT NULL auto_increment,
  provider varchar(255),
  url varchar(255),
  enterdate datetime,
  oai_identifier varchar(255),
  oai_set varchar(255),
  datestamp datetime,
  deleted enum('false', 'true') NOT NULL,
  dc_title varchar(255),
  dc_creator text,
  dc_subject varchar(255),
  dc_description text,
  dc_contributor varchar(255),
  dc_publisher varchar(255),
  dc_date date,
  dc_type varchar(255),
  dc_format varchar(255),
  dc_identifier varchar(255),
  dc_source varchar(255),
  dc_language varchar(255),
  dc_relation varchar(255),
  dc_coverage varchar(255),
  dc_rights varchar(255),
  PRIMARY KEY (serial)
);

