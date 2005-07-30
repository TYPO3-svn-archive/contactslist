#
# Table structure for table 'tx_contactslist_contacts'
#
CREATE TABLE tx_contactslist_contacts (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	company tinytext NOT NULL,
	contactperson tinytext NOT NULL,
	address1 tinytext NOT NULL,
	address2 tinytext NOT NULL,
	zipcode tinytext NOT NULL,
	city tinytext NOT NULL,
	country tinytext NOT NULL,
	phone tinytext NOT NULL,
	fax tinytext NOT NULL,
	mobile tinytext NOT NULL,
	homepage tinytext NOT NULL,
	email tinytext NOT NULL,
	zipprefixes tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);