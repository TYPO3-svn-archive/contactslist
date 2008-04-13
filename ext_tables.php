<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

t3lib_extMgm::allowTableOnStandardPages('tx_contactslist_contacts');

t3lib_extMgm::addToInsertRecords('tx_contactslist_contacts');

$TCA['tx_contactslist_contacts'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts',
		'label' => 'company',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_contactslist_contacts.gif'
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'hidden, starttime, endtime, company, contactperson, address1, address2, zipcode, city, country, phone, fax, mobile, homepage, email, zipprefixes'
	)
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']
	= 'layout,select_key';

t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:contactslist/locallang_db.xml:tt_content.list_type_pi1',
		$_EXTKEY.'_pi1'
	),
	'list_type'
);

t3lib_extMgm::addStaticFile(
	$_EXTKEY, 'pi1/static/', 'Contacts List'
);

?>
