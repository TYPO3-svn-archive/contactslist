<?php

########################################################################
# Extension Manager/Repository config file for ext: "contactslist"
#
# Auto generated 13-04-2008 14:58
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
	'dependencies' => 'cms,static_info_tables',
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
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.3.0',
	'_md5_values_when_last_written' => 'a:17:{s:9:"ChangeLog";s:4:"48d3";s:40:"class.tx_contactslist_templatehelper.php";s:4:"12f6";s:12:"ext_icon.gif";s:4:"2d12";s:17:"ext_localconf.php";s:4:"3e09";s:14:"ext_tables.php";s:4:"8b82";s:14:"ext_tables.sql";s:4:"b7ab";s:24:"icon_tx_contactslist.gif";s:4:"2d12";s:33:"icon_tx_contactslist_contacts.gif";s:4:"d930";s:16:"locallang_db.xml";s:4:"21d6";s:7:"tca.php";s:4:"c34c";s:8:"todo.txt";s:4:"a5e3";s:14:"doc/manual.sxw";s:4:"b13b";s:33:"pi1/class.tx_contactslist_pi1.php";s:4:"d24d";s:24:"pi1/contactslist_pi1.css";s:4:"4738";s:25:"pi1/contactslist_pi1.tmpl";s:4:"106a";s:17:"pi1/locallang.xml";s:4:"33f7";s:20:"pi1/static/setup.txt";s:4:"dba0";}',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.0.0-0.0.0',
			'cms' => '',
			'static_info_tables' => '2.0.2-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>