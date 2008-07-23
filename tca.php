<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_contactslist_contacts'] = array(
	'ctrl' => $TCA['tx_contactslist_contacts']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,starttime,endtime,company,contactperson,address1,address2,zipcode,city,country,phone,fax,mobile,homepage,email,zipprefixes'
	),
	'feInterface' => $TCA['tx_contactslist_contacts']['feInterface'],
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'company' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.company',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			),
		),
		'contactperson' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.contactperson',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'address1' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.address1',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'address2' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.address2',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'zipcode' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.zipcode',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '10',
			),
		),
		'city' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.city',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			),
		),
		'country' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.country',
			'config' => array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'required',
			),
		),
		'phone' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.phone',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			),
		),
		'fax' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.fax',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			),
		),
		'mobile' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.mobile',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			),
		),
		'homepage' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.homepage',
			'config' => array(
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					),
				),
			),
		),
		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
		'zipprefixes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.zipprefixes',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, company, contactperson, address1, address2, zipcode, city, country, phone, fax, mobile, homepage, email, zipprefixes'),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime'),
	),
);
?>