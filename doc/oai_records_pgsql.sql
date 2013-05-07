--
-- Table structure for table 'oai_records'
--
-- This is different to the structure for MySQL to show many structures can be choosen.
--

CREATE TABLE oai_records (
	serial serial,
	provider varchar(255),
	url varchar(255),
	enterdate timestamp,
	oai_identifier varchar(255),
	oai_set varchar(255),
	datestamp timestamp,
	deleted boolean default false,
	PRIMARY KEY (serial)
);
