<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('contactslist').'class.tx_contactslist_templatehelper.php');
require_once(t3lib_extMgm::extPath('sr_static_info').'pi1/class.tx_srstaticinfo_pi1.php');

class tx_contactslist_pi1 extends tx_contactslist_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_contactslist_pi1';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi1/class.tx_contactslist_pi1.php';
	/** The extension key. */
	var $extKey = 'contactslist';
	var $pi_checkCHash = TRUE;
	
	/** Static info library */
	var $staticInfo;
	
	/**
	 * Displays the contactslist HTML.
	 *
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * 
	 * @return	string		HTML for the plugin
	 * 
	 * @access public
	 */
	function main($content, $conf)	{
		$this->conf = $conf;
		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		// Loading the LOCAL_LANG values
		$this->pi_loadLL();
		
		// include CSS in header of page
		$GLOBALS['TSFE']->additionalHeaderData[] = '<style type="text/css" media="screen">@import "'.$this->conf['cssFile'].'";</style>';

		$this->getTemplateCode();
		
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
	 * @return	string		HTML for the plugin
	 * 
	 * @access protected
	 */
	function listView()	{
		/** Local settings for the listView function */
		$lConf = $this->conf['listView.'];	
	
		if (!isset($this->piVars['pointer'])) {
			$this->piVars['pointer'] = 0;
		}
		if (!isset($this->piVars['mode'])) {
			$this->piVars['mode'] = 1;
		}

		// Initializing the query parameters:
		list($this->internal['orderBy'], $this->internal['descFlag']) = explode(':', $this->piVars['sort']);
		
		// Number of results to show in a listing.
		$this->internal['results_at_a_time'] = t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,10);
		
		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['maxPages']          = t3lib_div::intInRange($lConf['maxPages'],0,1000,2);
		
		$this->internal['searchFieldList']   = 'company,contactperson,address1,address2,zipcode,city,phone,fax,mobile,email,zipprefixes';
		$this->internal['orderByList']       = 'uid,company,contactperson,address1,address2,zipcode,city,phone,fax,mobile,email,zipprefixes';

		$sameCountry = ' AND country="'.$GLOBALS['TYPO3_DB']->quoteStr($this->getSelectedCountry(), 'tx_contactslist_contacts').'"';

		$searchForZipCode = '';
		
		// Only search for ZIP prefixes if any ZIP code actually has been entered.
		// Else, let's display the whole list.
		if ($this->getEnteredZipCode()) {
			$searchForZipCode = ' AND zipprefixes REGEXP "(^|,| )'.$this->getZipRegExp().'($|,| )"';
		}

		// Get number of records
		$res = $this->pi_exec_query('tx_contactslist_contacts', 1, $sameCountry.$searchForZipCode);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tx_contactslist_contacts', 0, $sameCountry.$searchForZipCode);
		$this->internal['currentTable'] = 'tx_contactslist_contacts';

		// Remove fields from view
		$this->readSubpartsToHide($this->conf['hideFields'], 'WRAPPER');

		$this->setCSS(array('H3', 'Table', 'Td1', 'Td2', 'InputCountry', 'InputZipCode', 'SubmitButton'));
		
		// Put the whole list together:
		$fullTable = '';	// Clear var;

		// Add the country drop-down
		$fullTable .= $this->makeSearchbox();

		// Adds the whole list
		$fullTable .= $this->makelist($res);

		// Adds the result browser:
		$fullTable .= $this->makeResultBrowser();

		// Returns the content from the plugin.
		return $fullTable;
	}

	/**
	 * Creates the search box, containing a country selector, a zipcode search box
	 * and a submit button.
	 * 
	 * The form elements are already populated with the data given via GET (if there is any).
	 * 
	 * @return	String		HTML code for the search box
	 * 
	 * @access protected
	 */
	function makeSearchbox() {
		$this->setLabels(array('country', 'zipcode', 'submit', 'search'));
		$this->setMarkerContent('INTRO', $this->pi_getLL('intro'));
		$this->setMarkerContent('SELF_URL', $this->pi_linkTP_keepPIvars_url());
		
		$this->setMarkerContent('NAME_COUNTRYSELECT', $this->prefixId.'[country]');
		$this->setMarkerContent('ONCHANGE_COUNTRYSELECT', !empty($this->conf['onchangeCountryselect']) ? $this->conf['onchangeCountryselect'] : '');
		$this->setMarkerContent('OPTIONS_COUNTRYSELECT', $this->makeCountryItems($this->getSelectedCountry()));
		
		
		$this->setMarkerContent('NAME_ZIPCODE', $this->prefixId.'[zipcode]');
		$this->setMarkerContent('VALUE_ZIPCODE', $this->getEnteredZipCode());
		
		$result = $this->substituteMarkerArrayCached('SEARCH_FORM');
		
		return $result;
	}

	/**
	 * Gets the selected country - either from $this->piVars or, if there is nothing set,
	 * from the TS setup.
	 * 
	 * If no country is selected, an empty String is returned.
	 * 
	 * ToDo: Also return an empty string if there is no country with that code in the DB.
	 * 
	 * @return	String		ISO alpha3 code of the selected country
	 * 
	 * @access	public
	 */
	function getSelectedCountry() {
		$result = isset($this->piVars['country']) ? $this->piVars['country'] : $this->conf['defaultCountry'];
		
		return strtoupper($result);
	}

	/**
	 * Gets the ZIP code to search for (from $this->piVars). If there is nothing set,
	 * an empty string is returned
	 * 
	 * @return	String		trimmed ZIP code from the input form
	 * 
	 * @access	public
	 */
	function getEnteredZipCode() {
		$result = isset($this->piVars['zipcode']) ? $this->piVars['zipcode'] : '';
		
		return trim(htmlspecialchars($result));
	}

	/**
	 * Creates a list of <option> elements for countries that have a contact.
	 * 
	 * @param	String		ISO alpha3 code for the country to be pre-selected (may be empty)
	 * 
	 * @return	String		HTML code for the <option> elements (without the <select>)
	 * 
	 * @access	private
	 */
	function makeCountryItems($selectedCountry) {
		/** array of HTML <option> items with the localized names as keys (for sorting) */
		$names = array();
		
		// if nothing is selected, put an empty entry on top
		if (empty($selectedCountry)) {
			$this->setMarkerContent('VALUE_COUNTRYITEM', '');
			$this->setMarkerContent('SELECTED_COUNTRYITEM', '');
			$this->setMarkerContent('LOCALIZED_COUNTRYITEM', '');
			$names[' '] = $this->substituteMarkerArrayCached('ITEM_COUNTRYSELECT');
		}
		
		// Get entries for all countries that have a contact
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_iso_3', 'tx_contactslist_contacts, static_countries', 'tx_contactslist_contacts.country=static_countries.cn_iso_3');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$currentCountryCode = $row['cn_iso_3'];

				$this->setMarkerContent('VALUE_COUNTRYITEM', $currentCountryCode);
				$this->setMarkerContent('SELECTED_COUNTRYITEM', ($currentCountryCode === $selectedCountry) ? ' selected="selected"' : '');
				$countryName = $this->staticInfo->getStaticInfoName('COUNTRIES', $currentCountryCode);
				$this->setMarkerContent('LOCALIZED_COUNTRYITEM', $countryName);

				$names[$countryName] = $this->substituteMarkerArrayCached('ITEM_COUNTRYSELECT');
			}
		
		uksort($names, 'strcoll');
		return implode('', $names);
	}

	/**
	 * [Put your description here]
	 */
	function makelist($res)	{
		$items = array();
		// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}
	
		$out = '<div'.$this->pi_classParam('listrow').'>
			'.implode(chr(10), $items).'
			</div>';
		return $out;
	}
	
	/**
	 * Creates the HTML output for one contact.
	 * 
	 * @return	String	HTML output for one contact (shouldn't be empty)
	 * 
	 * @access	protected
	 */
	function makeListItem()	{
		$markerNames = array('company', 'contactperson', 'address1', 'address2', 'zipcode',
			'zipprefixes', 'city', 'country', 'phone', 'fax', 'mobile', 'homepage', 'email', 'zipprefixes');
			
		foreach ($markerNames as $currentMarkerName) {
			$fieldContent = $this->getFieldContent($currentMarkerName);
			if (!empty($fieldContent)) {
				$this->setMarkerContent($currentMarkerName, $fieldContent);
				$this->setLabels($currentMarkerName);
			} else {
				// If there is no data to display, just remove the empty line.
				$this->readSubpartsToHide($currentMarkerName, 'wrapper');
			}
		}
		
		// set table summary: 'Contact information: <company>'
		$this->setMarkerContent('contact_summary', $this->pi_getLL('contact_summary').': '.$this->getFieldContent('company'));
		
		return $this->substituteMarkerArrayCached('CONTACT_ITEM');
	}

	/**
	 * Creates the result browser.
	 * 
	 * @return	String		HTML code for the result browser
	 * 
	 * @access protected
	 */
	function makeResultBrowser() {
		$this->setMarkerContent('PIBASE_RESULTBROWSER', $this->pi_list_browseresults());
		
		return $this->substituteMarkerArrayCached('RESULTBROWSER_PART');
	}

	/**
	 * Writes localized labels into their corresponding template markers.
	 * 
	 * Example: For the $markerName 'company', the localized string with the key 'label_company'
	 * is written into the marker with the key '###LABEL_COMPANY###'.
	 * 
	 * @param	String		label name (see example), may also be an array of Strings
	 * 
	 * @access	protected
	 */
	function setLabels($markerName) {
		if (is_array($markerName)) {
			foreach ($markerName as $currentMarkerName) {
				$this->setLabels($currentMarkerName);
			}
		} else {
			$this->setMarkerContent($markerName, $this->pi_getLL('label_'.$markerName), 'label');
		}
		
		return;
	}

	/**
	 * Sets the CSS classes from TS for the template in $this->markers.
	 * 
	 * Classes are set only if they are set via TS, else the marker will be an empty string.
	 * 
	 * @param	array		array of Strings, list of TS setup variable names (without the prefix 'class') that contain the actual class names
	 * 
	 * @access	private
	 */
	function setCSS($setupNames) {
		foreach ($setupNames as $currentSetupName) {
			$className = isset($this->conf['class'.$currentSetupName]) ? ($this->conf['class'.$currentSetupName]) : '';
			if (!empty($className)) {
				$this->setMarkerContent($currentSetupName, $this->pi_classParam($className), 'class');
			} else {
				$this->setMarkerContent($currentSetupName, '', 'class');
			}
		}

		return; 
	}

	/**
	 * Creates a regular expression that searches strings of comma-separated ZIP prefixes
	 * for the entered ZIP code.
	 * 
	 * @return	String		a regular expression (withouth the delimiting slashes)
	 * 
	 * @access	private
	 */
	function getZipRegExp() {
		$enteredZipCode = $this->getEnteredZipCode();
		if ($enteredZipCode !== $GLOBALS['TYPO3_DB']->quoteStr($enteredZipCode, 'tx_contactslist_contacts')) {
			// If the string contains DB-dangerous strings, don't process it,
			// as the prefixing could destroy some DB escape strings.
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
	 * @param	String		key of the field for which the content should be retrieved
	 * 
	 * @return	String		the field content (may be empty)
	 * 
	 * @access protected
	 */
	function getFieldContent($fN)	{
		$result = '';
		
		switch($fN) {
			case 'country':
				$result = $this->staticInfo->getStaticInfoName('COUNTRIES', strtoupper($this->internal['currentRow']['country']));
				break;
			case 'homepage':
				$url = $this->internal['currentRow'][$fN];
				$result = $this->cObj->getTypoLink($url, $url);
				break;
			case 'email':
				$result = $this->cObj->mailto_makelinks('mailto:'.$this->internal['currentRow'][$fN], array());
				break;
			case 'phone':
			case 'fax':
			case 'mobile':
				$countryCode = strtoupper($this->internal['currentRow']['country']);
				// get phone prefix for country code
				$queryResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_phone', 'static_countries', 'cn_iso_3="'.trim($countryCode).'"');
				$row         = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResult);
				// if the phone prefix is not stored in the DB (or it is wrong), show question marks
				$phonePrefix = '+'.(isset($row['cn_phone']) ? $row['cn_phone'] : '???').'&nbsp;';
				
				// only show the prefix if we have a phone number
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