<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Plugin 'Contacts List' for the 'contactslist' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_contactslist
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('contactslist').'class.tx_contactslist_templatehelper.php');

require_once(t3lib_extMgm::extPath('sr_static_info').'pi1/class.tx_srstaticinfo_pi1.php');

class tx_contactslist_pi1 extends tx_contactslist_templatehelper {
	/** same as class name */
	var $prefixId = 'tx_contactslist_pi1';

	/**  path to this script relative to the extension directory */
	var $scriptRelPath = 'pi1/class.tx_contactslist_pi1.php';

	/** the extension key */
	var $extKey = 'contactslist';

	var $pi_checkCHash = true;

	/** static info library */
	var $staticInfo = null;

	/**
	 * Displays the contacts list HTML.
	 *
	 * @param	string		(unused)
	 * @param	array		TypoScript configuration for the plugin
	 *
	 * @return	string		HTML for the plugin, will not be empty
	 *
	 * @access public
	 */
	function main($unused, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// includes the CSS in the page header
		if ($this->getConfValue('cssFile') !== '') {
			$GLOBALS['TSFE']->additionalHeaderData[]
				= '<style type="text/css">@import "'
					.$this->getConfValue('cssFile').'";</style>';
		}

		$this->getTemplateCode();
		$this->setLabels();

		$this->staticInfo = t3lib_div::makeInstance('tx_srstaticinfo_pi1');
        $this->staticInfo->init();

		if (strstr($this->cObj->currentRecord,'tt_content')) {
			$this->conf['pidList'] = $this->cObj->data['pages'];
			$this->conf['recursive'] = $this->cObj->data['recursive'];
		}

		return $this->pi_wrapInBaseClass($this->listView());
	}

	/**
	 * Displays a list of contacts.
	 *
	 * @return	string		HTML for the plugin, will not be empty
	 *
	 * @access protected
	 */
	function listView()	{
		/** local settings for the listView function */
		$lConf = $this->conf['listView.'];

		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}
		if (!isset($this->piVars['mode'])) {
			$this->piVars['mode'] = 1;
		}

		// initializes the query parameters
		list($this->internal['orderBy'], $this->internal['descFlag'])
			= explode(':', $this->piVars['sort']);

		// number of results to show in a listing
		$this->internal['results_at_a_time'] = t3lib_div::intInRange(
			$lConf['results_at_a_time'], 0, 1000, 10
		);

		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2"
		// etc.
		$this->internal['maxPages'] = t3lib_div::intInRange(
			$lConf['maxPages'], 0, 1000, 2
		);

		$sameCountry = ' AND country="'.$this->getSelectedCountry().'"';

		$searchForZipCode = '';

		// Only searches for ZIP prefixes if any ZIP code actually has been
		// entered. Otherwise, displays the full list.
		if ($this->getEnteredZipCode()) {
			$searchForZipCode = ' AND zipprefixes REGEXP "(^|,| )'
				.$this->getZipRegExp().'($|,| )"';
		}

		// Gets the number of records.
		$res = $this->pi_exec_query(
			'tx_contactslist_contacts', 1, $sameCountry.$searchForZipCode
		);
		list($this->internal['res_count'])
			= $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Makes the listing query and passes the query to SQL database.
		$res = $this->pi_exec_query(
			'tx_contactslist_contacts', 0, $sameCountry.$searchForZipCode
		);
		$this->internal['currentTable'] = 'tx_contactslist_contacts';

		// Removes fields from view.
		$this->readSubpartsToHide($this->getConfValue('hideFields'), 'WRAPPER');
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
	 * @return	String		HTML code for the search box
	 *
	 * @access protected
	 */
	function makeSearchbox() {
		$this->setMarkerContent('INTRO', $this->pi_getLL('intro'));
		$this->setMarkerContent('SELF_URL', $this->pi_linkTP_keepPIvars_url());

		$this->setMarkerContent('NAME_COUNTRYSELECT', $this->prefixId.'[country]');
		$this->setMarkerContent(
			'ONCHANGE_COUNTRYSELECT',
			$this->getConfValue('onchangeCountryselect')
		);
		$this->setMarkerContent(
			'OPTIONS_COUNTRYSELECT',
			$this->makeCountryItems($this->getSelectedCountry())
		);


		$this->setMarkerContent('NAME_ZIPCODE', $this->prefixId.'[zipcode]');
		$this->setMarkerContent('VALUE_ZIPCODE', $this->getEnteredZipCode());

		return $this->substituteMarkerArrayCached('SEARCH_FORM');
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
	 * @return	String		ISO alpha3 code of the selected country
	 *
	 * @access	public
	 */
	function getSelectedCountry() {
		$resultRaw = isset($this->piVars['country'])
			? $this->piVars['country'] : $this->getConfValue('defaultCountry');
		$resultQuoted = strtoupper(
			$GLOBALS['TYPO3_DB']->quoteStr($resultRaw, 'static_countries')
		);

		// Gets the number of records.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			'static_countries',
			'cn_iso_3="'.$resultQuoted.'"'
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// Only uses the result if there is a DB record for it.
		$result = ($row['num']) ? $resultQuoted : '';

		return $result;
	}

	/**
	 * Gets the ZIP code to search for (from $this->piVars). If there is nothing
	 * set, an empty string is returned
	 *
	 * @return	string		trimmed ZIP code from the input form, might be empty
	 *
	 * @access	public
	 */
	function getEnteredZipCode() {
		$result = isset($this->piVars['zipcode'])
			? $this->piVars['zipcode'] : '';

		return trim(htmlspecialchars($result));
	}

	/**
	 * Creates a list of <option> elements for countries that have a contact.
	 *
	 * @param	string		ISO alpha3 code for the country to be pre-selected,
	 * 						may be empty
	 *
	 * @return	string		HTML code for the <option> elements (without the
	 * 						<select>), will not be empty
	 *
	 * @access	private
	 */
	function makeCountryItems($selectedCountry) {
		/** array of HTML <option> items with the localized names as keys (for
		 *  sorting) */
		$names = array();

		// If nothing is selected, puts an empty entry on top.
		if (empty($selectedCountry)) {
			$this->setMarkerContent('VALUE_COUNTRYITEM', '');
			$this->setMarkerContent('SELECTED_COUNTRYITEM', '');
			$this->setMarkerContent('LOCALIZED_COUNTRYITEM', '');
			$names[' '] = $this->substituteMarkerArrayCached('ITEM_COUNTRYSELECT');
		}

		// Gets the entries for all countries that have a contact.
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'cn_iso_3',
			'tx_contactslist_contacts, static_countries',
			'tx_contactslist_contacts.country=static_countries.cn_iso_3'
		);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$currentCountryCode = $row['cn_iso_3'];

			$this->setMarkerContent('VALUE_COUNTRYITEM', $currentCountryCode);
			$this->setMarkerContent(
				'SELECTED_COUNTRYITEM',
				($currentCountryCode === $selectedCountry)
					? ' selected="selected"' : ''
			);
			$countryName = $this->staticInfo->getStaticInfoName(
				'COUNTRIES', $currentCountryCode
			);
			$this->setMarkerContent('LOCALIZED_COUNTRYITEM', $countryName);

			$names[$countryName] = $this->substituteMarkerArrayCached('ITEM_COUNTRYSELECT');
		}

		// sorts by localized names
		uksort($names, 'strcoll');
		return implode('', $names);
	}

	/**
	 * Creates a nice list of contacts.
	 *
	 * @param	ressource	DB result containing the data items to be displayed
	 *
	 * @return	string		HTML code containing the list, will not be empty
	 *
	 * @access protected
	 */
	function makelist($res)	{
		$items = array();

		while($this->internal['currentRow']
			= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)
		) {
			$items[]=$this->makeListItem();
		}

		return implode(chr(10), $items);
	}

	/**
	 * Creates the HTML output for a single contact.
	 *
	 * @return	string		HTML output for one contact, will not be empty
	 *
	 * @access	protected
	 */
	function makeListItem()	{
		$markerNames = array(
			'company', 'contactperson', 'address1', 'address2', 'zipcode',
			'zipprefixes', 'city', 'country', 'phone', 'fax', 'mobile',
			'homepage', 'email', 'zipprefixes'
		);

		foreach ($markerNames as $currentMarkerName) {
			$fieldContent = $this->getFieldContent($currentMarkerName);
			if (!empty($fieldContent)) {
				$this->setMarkerContent($currentMarkerName, $fieldContent);
			} else {
				// If there is no data to display, just removes the empty line.
				$this->readSubpartsToHide($currentMarkerName, 'wrapper');
			}
		}

		// Set the table summary: 'Contact information: <company>'
		$this->setMarkerContent(
			'contact_summary',
			$this->pi_getLL('contact_summary').': '
				.$this->getFieldContent('company')
		);

		return $this->substituteMarkerArrayCached('CONTACT_ITEM');
	}

	/**
	 * Creates the result browser.
	 *
	 * @return	string		HTML code for the result browser, will not be empty
	 *
	 * @access protected
	 */
	function makeResultBrowser() {
		$this->setMarkerContent(
			'PIBASE_RESULTBROWSER', $this->pi_list_browseresults()
		);

		return $this->substituteMarkerArrayCached('RESULTBROWSER_PART');
	}

	/**
	 * Creates a regular expression that searches strings of comma-separated ZIP
	 * prefixes for the entered ZIP code.
	 *
	 * @return	string		a regular expression (withouth the delimiting
	 * 						slashes)
	 *
	 * @access	private
	 */
	function getZipRegExp() {
		$enteredZipCode = $this->getEnteredZipCode();
		if ($enteredZipCode !== $GLOBALS['TYPO3_DB']->quoteStr(
			$enteredZipCode, 'tx_contactslist_contacts')
		) {
			// If the string contains DB-dangerous strings, just doesn't process
			// it because the prefixing could destroy some DB escape strings.
			$enteredZipCode = '';
		}
		$listOfPrefixes = '';

		for ($i = 1; $i <= strlen($enteredZipCode); $i++) {
			if (!empty($listOfPrefixes)) {
				$listOfPrefixes .= '|';
			}

			$listOfPrefixes .= substr($enteredZipCode, 0, $i);
		}

		return '('.$listOfPrefixes.')';
	}

	/**
	 * Gets the content for a field (e.g. company name).
	 *
	 * @param	string		key of the field for which the content should be
	 * 						retrieved, must not be empty
	 *
	 * @return	string		the field content, might be empty
	 *
	 * @access protected
	 */
	function getFieldContent($fN)	{
		$result = '';

		switch($fN) {
			case 'country':
				$result = $this->staticInfo->getStaticInfoName(
					'COUNTRIES',
					strtoupper($this->internal['currentRow']['country'])
				);
				break;
			case 'homepage':
				$url = $this->internal['currentRow'][$fN];
				$result = $this->cObj->getTypoLink($url, $url);
				break;
			case 'email':
				$result = $this->cObj->mailto_makelinks(
					'mailto:'.$this->internal['currentRow'][$fN], array()
				);
				break;
			case 'phone':
			case 'fax':
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
				// if the phone prefix is not stored in the DB (or it is wrong),
				// shows question marks
				$phonePrefix = '+'.(isset($row['cn_phone'])
					? $row['cn_phone'] : '???').'&nbsp;';

				// only shows the prefix if we have a phone number
				$result = $this->internal['currentRow'][$fN];
				if (!empty($result)) {
					$result = $phonePrefix.$result;
				}
				break;
			default:
				$result = $this->internal['currentRow'][$fN];
				break;
		}

		return $result;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/contactslist/pi1/class.tx_contactslist_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/contactslist/pi1/class.tx_contactslist_pi1.php']);
}

?>