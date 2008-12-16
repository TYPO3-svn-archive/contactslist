<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Testcase for the tx_contactslist_pi1 class in the 'contactslist' extension.
 *
 * @package TYPO3
 * @subpackage tx_contactslist
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

require_once(t3lib_extMgm::extPath('contactslist').'pi1/class.tx_contactslist_pi1.php');

class tx_contactslist_pi1_testcase extends tx_phpunit_testcase {
	/** the name of our main data table */
	const table = 'tx_contactslist_contacts';

	private $fixture;
	private $testingFramework;

	/** PID of a dummy system folder */
	private $systemFolderPid = 0;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_contactslist');
		$this->testingFramework->createFakeFrontEnd();

		$this->systemFolderPid = $this->testingFramework->createSystemFolder();

		$this->fixture = new tx_contactslist_pi1();

		$this->fixture->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:contactslist/pi1/contactslist_pi1.html',
				'pidList' => $this->systemFolderPid,
				'defaultCountry' => 'DEU',
				'listView.' => array(
					'results_at_a_time' => 10,
					'maxPages' => 5,
					'orderBy' => 'company',
					'descFlag' => false,
				)
			)
		);

	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////////////
	// Test concerning the base functionality.
	////////////////////////////////////////////

	public function testListViewContainsForm() {
		$this->assertContains(
			'<form',
			$this->fixture->main('', array())
		);
	}


	/////////////////////////////
	// Tests for the list view.
	/////////////////////////////

	public function testListViewConContainOneItem() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company 1',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'company 1',
			$this->fixture->main('', array())
		);
	}

	public function testListViewConContainTwoItems() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company 1',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company 2',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$output = $this->fixture->main('', array());
		$this->assertContains(
			'company 1',
			$output
		);
		$this->assertContains(
			'company 2',
			$output
		);
	}

	public function testListViewCanBeSortedAscendingByCompanyWithSameOrder() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company A',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company B',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$output = $this->fixture->main('', array());
		$this->assertTrue(
			strpos($output, 'company A') < strpos($output, 'company B')
		);
	}

	public function testListViewCanBeSortedAscendingByCompanyNameWithReversedOrder() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company B',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company A',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$output = $this->fixture->main('', array());
		$this->assertTrue(
			strpos($output, 'company A') < strpos($output, 'company B')
		);
	}

	public function testNonEmptyHomepageCanFollowEmptyHomepage() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company A',
				'homepage' => '',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company B',
				'homepage' => 'http://www.foo.com/',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'http://www.foo.com/',
			$this->fixture->main('', array())
		);
	}

	public function testNonEmptyPhoneCanFollowEmptyPhone() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company A',
				'phone' => '',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company B',
				'phone' => '1234 56789',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'1234 56789',
			$this->fixture->main('', array())
		);
	}

	public function testNonEmptyFaxCanFollowEmptyFax() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company A',
				'fax' => '',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);
		$this->testingFramework->createRecord(
			self::table,
			array(
				'company' => 'company B',
				'fax' => '1234 56789',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'1234 56789',
			$this->fixture->main('', array())
		);
	}


	////////////////////////////////////////
	// Tests concerning the e-mail address
	////////////////////////////////////////

	public function testListViewShowsNonEmptyEMailAddress() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'email' => 'foo@bar.com',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'foo@bar.com',
			$this->fixture->main('', array())
		);
	}

	public function testListViewForNonEmptyEMailAddressContainsMailto() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'email' => 'foo@bar.com',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertContains(
			'mailto:',
			$this->fixture->main('', array())
		);
	}

	public function testListViewForNonEmptyEMailAddressNotContainsMailto() {
		$this->testingFramework->createRecord(
			self::table,
			array(
				'email' => '',
				'country' => 'DEU',
				'pid' => $this->systemFolderPid,
			)
		);

		$this->assertNotContains(
			'mailto:',
			$this->fixture->main('', array())
		);
	}
}
?>