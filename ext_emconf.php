<?php

########################################################################
# Extension Manager/Repository config file for ext: "contactslist"
#
# Auto generated 13-04-2008 13:26
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Contacts List',
	'description' => 'Display a list of contacts, e.g. of sales offices. The list is searchable by ZIP code and by country.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms,sr_static_info',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.3.0',
	'_md5_values_when_last_written' => 'a:17:{s:9:"ChangeLog";s:4:"7803";s:40:"class.tx_contactslist_templatehelper.php";s:4:"e24d";s:12:"ext_icon.gif";s:4:"2d12";s:17:"ext_localconf.php";s:4:"3400";s:14:"ext_tables.php";s:4:"b116";s:14:"ext_tables.sql";s:4:"bb9d";s:24:"ext_typoscript_setup.txt";s:4:"dba0";s:24:"icon_tx_contactslist.gif";s:4:"2d12";s:33:"icon_tx_contactslist_contacts.gif";s:4:"d930";s:16:"locallang_db.xml";s:4:"21d6";s:7:"tca.php";s:4:"6327";s:8:"todo.txt";s:4:"5260";s:14:"doc/manual.sxw";s:4:"23d8";s:33:"pi1/class.tx_contactslist_pi1.php";s:4:"eec0";s:24:"pi1/contactslist_pi1.css";s:4:"b363";s:25:"pi1/contactslist_pi1.tmpl";s:4:"106a";s:17:"pi1/locallang.xml";s:4:"ecf2";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'sr_static_info' => '',
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>