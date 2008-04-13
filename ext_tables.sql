#
# Table structure for table 'tx_contactslist_contacts'
#
CREATE TABLE tx_contactslist_contacts (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(1) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	company tinytext,
	contactperson tinytext,
	address1 tinytext,
	address2 tinytext,
	zipcode tinytext,
	city tinytext,
	country tinytext,
	phone tinytext,
	fax tinytext,
	mobile tinytext,
	homepage tinytext,
	email tinytext,
	zipprefixes tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);
