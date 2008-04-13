<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_contactslist_contacts'] = Array(
	'ctrl' => $TCA['tx_contactslist_contacts']['ctrl'],
	'interface' => Array(
		'showRecordFieldList' => 'hidden,starttime,endtime,company,contactperson,address1,address2,zipcode,city,country,phone,fax,mobile,homepage,email,zipprefixes'
	),
	'feInterface' => $TCA['tx_contactslist_contacts']['feInterface'],
	'columns' => Array(
		'hidden' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => Array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => Array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => Array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'company' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.company',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'contactperson' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.contactperson',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'address1' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.address1',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'address2' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.address2',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'zipcode' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.zipcode',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'max' => '10',
			)
		),
		'city' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.city',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'country' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.country',
			'config' => Array(
				'type' => 'input',
				'size' => '3',
				'max' => '3',
				'eval' => 'required',
			)
		),
		'phone' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.phone',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
		'fax' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.fax',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
		'mobile' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.mobile',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
		'homepage' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.homepage',
			'config' => Array(
				'type' => 'input',
				'size' => '15',
				'max' => '255',
				'checkbox' => '',
				'eval' => 'trim',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'email' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.email',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'zipprefixes' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contactslist/locallang_db.xml:tx_contactslist_contacts.zipprefixes',
			'config' => Array(
				'type' => 'input',
				'size' => '30',
			)
		),
	),
	'types' => Array(
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, company, contactperson, address1, address2, zipcode, city, country, phone, fax, mobile, homepage, email, zipprefixes')
	),
	'palettes' => Array(
		'1' => Array('showitem' => 'starttime, endtime')
	)
);
?>