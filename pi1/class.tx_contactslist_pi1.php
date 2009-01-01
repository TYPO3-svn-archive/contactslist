<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Plugin 'Contacts List' for the 'contactslist' extension.
 *
 * @package TYPO3
 * @subpackage tx_contactslist
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');
require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');

require_once(t3lib_extMgm::extPath('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');

class tx_contactslist_pi1 extends tx_oelib_templatehelper {
	/** same as class name */
	public $prefixId = 'tx_contactslist_pi1';

	/**  path to this script relative to the extension directory */
	public $scriptRelPath = 'pi1/class.tx_contactslist_pi1.php';

	/** the extension key */
	public $extKey = 'contactslist';

	public $pi_checkCHash = true;

	/** an instance of tx_staticinfotables_pi1 */
	private $staticInfo = null;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->staticInfo);
		parent::__destruct();
	}

	/**
	 * Displays the contacts list HTML.
	 *
	 * @param string (unused)
	 * @param array TypoScript configuration for the plugin, may be empty
	 *
	 * @return string HTML for the plugin, will not be empty
	 */
	public function main($unused, array $configuration) {
		$this->init($configuration);

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();

		$this->initStaticInfo();

		if (strstr($this->cObj->currentRecord,'tt_content')) {
			$this->conf['pidList'] = $this->cObj->data['pages'];
			$this->conf['recursive'] = $this->cObj->data['recursive'];
		}

		return $this->pi_wrapInBaseClass($this->listView());
	}

	/**
	 * Displays a list of contacts.
	 *
	 * @return string HTML for the plugin, will not be empty
	 */
	private function listView() {
		$this->internal['orderByList']
			= 'company,contactperson,zipcode,city,country';

		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}

		$this->internal['orderBy']
			= $this->getListViewConfValueString('orderBy');
		$this->internal['descFlag']
			= $this->getListViewConfValueBoolean('descFlag');

		$this->internal['results_at_a_time'] = t3lib_div::intInRange(
			$this->getListViewConfValueInteger('results_at_a_time'), 0, 1000, 10
		);

		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2"
		// etc.
		$this->internal['maxPages'] = t3lib_div::intInRange(
			$this->getListViewConfValueInteger('maxPages'), 0, 1000, 2
		);

		$sameCountry = ' AND country="'.$this->getSelectedCountry().'"';

		// Only searches for ZIP prefixes if any ZIP code actually has been
		// entered. Otherwise, displays the full list.
		if ($this->getEnteredZipCode() != '') {
			$searchForZipCode = ' AND zipprefixes REGEXP "(^|,| )' .
				$this->getZipRegExp() . '($|,| )"';
		} else {
			$searchForZipCode = '';
		}

		// Gets the number of records.
		$res = $this->pi_exec_query(
			'tx_contactslist_contacts', 1, $sameCountry . $searchForZipCode
		);
		list($this->internal['res_count'])
			= $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Makes the listing query and passes the query to SQL database.
		$res = $this->pi_exec_query(
			'tx_contactslist_contacts', 0, $sameCountry . $searchForZipCode
		);
		$this->internal['currentTable'] = 'tx_contactslist_contacts';

		// Removes fields from view.
		$this->hideSubparts($this->getConfValueString('hideFields'), 'WRAPPER');
		$this->setCSS();

		// Puts the whole list together.
		$fullTable = $this->makeSearchbox();
		$fullTable .= $this->makelist($res);
		$fullTable .= $this->makeResultBrowser();

		return $fullTable;
	}

	/**
	 * Creates the search box, containing a country selector, a ZIP code search
	 * box and a submit button.
	 *
	 * The form elements are already populated with the data given via GET (if
	 * there is any).
	 *
	 * @return string HTML code for the search box, will not be empty
	 */
	private function makeSearchbox() {
		$this->setMarker('intro', $this->translate('intro'));
		$this->setMarker('self_url', $this->pi_linkTP_keepPIvars_url());

		$this->setMarker('name_countryselect', $this->prefixId.'[country]');
		$this->setMarker(
			'onchange_countryselect',
			$this->getConfValueString('onchangeCountryselect')
		);
		$this->setMarker(
			'options_countryselect',
			$this->makeCountryItems($this->getSelectedCountry())
		);


		$this->setMarker('name_zipcode', $this->prefixId.'[zipcode]');
		$this->setMarker(
			'value_zipcode', htmlspecialchars($this->getEnteredZipCode())
		);

		return $this->getSubpart('SEARCH_FORM');
	}

	/**
	 * Gets the code for the selected country - either from $this->piVars or, if
	 * there is nothing set, from the TS setup.
	 *
	 * If no country is selected, an empty String is returned.
	 *
	 * Also an empty string is returned if there is no country with that code in
	 * the list of countries in the DB.
	 *
	 * The return value is quoted (although that shouldn't be necessary anyway).
	 *
	 * @return string ISO alpha3 code of the selected country
	 */
	private function getSelectedCountry() {
		$resultRaw = isset($this->piVars['country'])
			? $this->piVars['country']
			: $this->getConfValueString('defaultCountry');
		$resultQuoted = strtoupper(
			$GLOBALS['TYPO3_DB']->quoteStr($resultRaw, 'static_countries')
		);

		// Gets the number of records.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			'static_countries',
			'cn_iso_3="'.$resultQuoted.'"'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		// Only uses the result if there is a DB record for it.
		$result = ($row['number']) ? $resultQuoted : '';

		return $result;
	}

	/**
	 * Gets the ZIP code to search for (from $this->piVars). If there is nothing
	 * set, an empty string is returned.
	 *
	 * @return string trimmed ZIP code from the input form, might be empty
	 */
	private function getEnteredZipCode() {
		return (isset($this->piVars['zipcode']))
			? trim($this->piVars['zipcode']) : '';
	}

	/**
	 * Creates a list of <option> elements for countries that have a contact.
	 *
	 * @param string ISO alpha3 code for the country to be pre-selected,
	 *               may be empty
	 *
	 * @return string HTML code for the <option> elements (without the
	 *                <select>), will not be empty
	 */
	private function makeCountryItems($selectedCountry) {
		/** array of HTML <option> items with the localized names as keys (for
		 *  sorting) */
		$names = array();

		// If nothing is selected, puts an empty entry on top.
		if (empty($selectedCountry)) {
			$this->setMarker('value_countryitem', '');
			$this->setMarker('selected_countryitem', '');
			$this->setMarker('localized_countryitem', '');
			$names[' '] = $this->getSubpart('ITEM_COUNTRYSELECT');
		}

		// Gets the entries for all countries that have a contact.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'cn_iso_3',
			'tx_contactslist_contacts, static_countries',
			'tx_contactslist_contacts.country=static_countries.cn_iso_3'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			$currentCountryCode = $row['cn_iso_3'];

			$this->setMarker('value_countryitem', $currentCountryCode);
			$this->setMarker(
				'selected_countryitem',
				($currentCountryCode === $selectedCountry)
					? ' selected="selected"' : ''
			);
			$countryName = $this->staticInfo->getStaticInfoName(
				'COUNTRIES', $currentCountryCode
			);
			$this->setMarker('localized_countryitem', $countryName);

			$names[$countryName] = $this->getSubpart('ITEM_COUNTRYSELECT');
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		// sorts by localized names
		uksort($names, 'strcoll');
		return implode('', $names);
	}

	/**
	 * Creates a nice list of contacts.
	 *
	 * @param ressource DB result containing the data items to be displayed
	 *
	 * @return string HTML code containing the list, will not be empty
	 */
	private function makelist($resource) {
		$items = array();

		while($this->internal['currentRow']
			= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)
		) {
			$items[] = $this->makeListItem();
		}

		return implode(LF, $items);
	}

	/**
	 * Creates the HTML output for a single contact.
	 *
	 * @return string HTML output for one contact, will not be empty
	 */
	private function makeListItem() {
		$markerNames = array(
			'company', 'contactperson', 'address1', 'address2', 'zipcode',
			'zipprefixes', 'city', 'country', 'phone', 'fax', 'mobile',
			'homepage', 'email', 'zipprefixes'
		);

		// Makes sure all wanted subparts are visible to start with.
		$this->unhideSubparts(
			implode(',', $markerNames),
			$this->getConfValueString('hideFields'),
			'wrapper'
		);

		foreach ($markerNames as $markerName) {
			$fieldContent = $this->getFieldContent($markerName);
			if ($fieldContent != '') {
				$this->setMarker($markerName, $fieldContent);
			} else {
				// If there is no data to display, just removes the empty line.
				$this->hideSubparts($markerName, 'wrapper');
			}
		}

		// Set the table summary: 'Contact information: <company>'
		$this->setMarker(
			'contact_summary',
			$this->translate('contact_summary').': '
				.$this->getFieldContent('company')
		);

		return $this->getSubpart('CONTACT_ITEM');
	}

	/**
	 * Creates the result browser.
	 *
	 * @return string HTML code for the result browser, will not be empty
	 */
	private function makeResultBrowser() {
		$this->setMarker(
			'pibase_resultbrowser', $this->pi_list_browseresults()
		);

		return $this->getSubpart('RESULTBROWSER_PART');
	}

	/**
	 * Creates a regular expression that searches strings of comma-separated ZIP
	 * prefixes for the entered ZIP code.
	 *
	 * @return string a regular expression (withouth the delimiting slashes),
	 *                will be not empty
	 */
	private function getZipRegExp() {
		$enteredZipCode = $this->getEnteredZipCode();
		if ($enteredZipCode != $GLOBALS['TYPO3_DB']->quoteStr(
			$enteredZipCode, 'tx_contactslist_contacts')
		) {
			// If the string contains DB-dangerous strings, just doesn't process
			// it because the prefixing could destroy some DB escape strings.
			return '()';
		}

		$prefixes = array();

		for ($i = 1; $i <= strlen($enteredZipCode); $i++) {
			$prefixes[] = substr($enteredZipCode, 0, $i);
		}

		return '(' . implode('|', $prefixes) . ')';
	}

	/**
	 * Gets the content for a field (e.g. company name).
	 *
	 * @param string key of the field for which the content should be
	 *               retrieved, must not be empty
	 *
	 * @return string the field content, might be empty
	 */
	private function getFieldContent($key) {
		$result = '';

		switch($key) {
			case 'country':
				$result = $this->staticInfo->getStaticInfoName(
					'COUNTRIES',
					strtoupper($this->internal['currentRow']['country'])
				);
				break;
			case 'homepage':
				$url = $this->internal['currentRow'][$key];
				$result = $this->cObj->getTypoLink($url, $url);
				break;
			case 'email':
				$eMailAddress = $this->internal['currentRow'][$key];

				$result = ($eMailAddress != '')
					? $this->cObj->mailto_makelinks(
						'mailto:' . $eMailAddress, array())
					: '';
				break;
			case 'phone':
				// The fall-through is intended.
			case 'fax':
				// The fall-through is intended.
			case 'mobile':
				$countryCode
					 = strtoupper($this->internal['currentRow']['country']);
				// gets phone prefix for country code
				$queryResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'cn_phone',
					'static_countries',
					'cn_iso_3="'.trim($countryCode).'"'
				);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResult);
				$GLOBALS['TYPO3_DB']->sql_free_result($queryResult);
				// if the phone prefix is not stored in the DB (or it is wrong),
				// shows question marks
				$phonePrefix = '+'.(isset($row['cn_phone'])
					? $row['cn_phone'] : '???').'&nbsp;';

				// only shows the prefix if we have a phone number
				$result = $this->internal['currentRow'][$key];
				if (!empty($result)) {
					$result = $phonePrefix.$result;
				}
				break;
			default:
				$result = $this->internal['currentRow'][$key];
				break;
		}

		return $result;
	}

	/**
	 * Creates and initializes $this->staticInfo (if that hasn't been done yet).
	 */
	private function initStaticInfo() {
		if (!$this->staticInfo) {
			$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/contactslist/pi1/class.tx_contactslist_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/contactslist/pi1/class.tx_contactslist_pi1.php']);
}
?>