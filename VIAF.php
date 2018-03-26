<?php
/** ---------------------------------------------------------------------
 * app/lib/core/Plugins/InformationService/VIAF.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2015 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage InformationService
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */

  /**
    *
    */


require_once(__CA_LIB_DIR__ . "/core/Plugins/IWLPlugInformationService.php");
require_once(__CA_LIB_DIR__ . "/core/Plugins/InformationService/BaseInformationServicePlugin.php");

global $g_information_service_settings_Viaf;
$g_information_service_settings_Viaf = array(
	'lang' => array(
		'formatType' => FT_TEXT,
		'displayType' => DT_FIELD,
		'url' => 'http://www.viaf.org/viaf',
		'width' => 90, 'height' => 1,
		'label' => _t('Viaf service URL'),
		'description' => _t('URL of services.php to the specific vocabulary dir of Viaf. DO NOT include trailing slash index.php or services.php, only base address to the dir containing index.php.')
	),
);

$json = '{
  "_ns": {
    "dct": "http://purl.org/dc/terms/",
    "schema": "http://schema.org/",
    "rdf": "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  },
  "_uriSpace": {
    "Concept": {
      "uriSpace": "http://viaf.org/viaf/",
      "notationPattern": "/^[0-9]+$/"
    }
  },
  "type": {
    "type": "URI",
    "properties": [
      "rdf:type"
    ]
  },
  "identifier": {
    "type": "URI",
    "properties": [
      "schema:sameAs"
    ]
  },
  "notation": {
    "type": "plain",
    "properties": [
      "dct:identifier"
    ]
  },
  "prefLabel": {
    "type": "literal",
    "unique": true,
    "properties": [
      "schema:name"
    ]
  },
  "startDate": {
    "type": "plain",
    "unique": true,
    "properties": [
      "schema:birthDate"
    ]
  },
  "endDate": {
    "type": "plain",
    "unique": true,
    "properties": [
      "schema:deathDate"
    ]
  }
}';
$result = json_decode ($json);

class WLPlugInformationServiceViaf Extends BaseInformationServicePlugin Implements IWLPlugInformationService {
	# ------------------------------------------------
	static $s_settings;
	# ------------------------------------------------
	/**
	 *
	 */
	public function __construct() {
		global $g_information_service_settings_Viaf;

		WLPlugInformationServiceViaf::$s_settings = $g_information_service_settings_Viaf;
		parent::__construct();
		$this->info['NAME'] = 'Viaf';

		$this->description = _t('Provides access to Viaf');
	}
	# ------------------------------------------------
	/**
	 * Get all settings settings defined by this plugin as an array
	 *
	 * @return array
	 */
	public function getAvailableSettings() {
		return WLPlugInformationServiceViaf::$s_settings;
	}
	# ------------------------------------------------
	# Data
	# ------------------------------------------------
	/**
	 * Perform lookup on Viaf-based data service
	 *
	 * @param array $pa_settings Plugin settings values
	 * @param string $ps_search The expression with which to query the remote data service
	 * @param array $pa_options Lookup options (none defined yet)
	 * @return array
	 */
	public function lookup($pa_settings, $ps_search, $pa_options=null) {

        // support passing full viaf URLs
		//if(isURL($ps_search)) { $ps_search = self::getPageTitleFromURI($ps_search); }
		$vs_url = caGetOption('url', $pa_settings, 'http://www.viaf.org/viaf');
		//var_dump($ps_search);die();
		// readable version of get parameters
		$va_get_params = array(
			'query' => urlencode($ps_search)
		);
		$vs_content = caQueryExternalWebservice(
			$vs_url_search = $vs_url ."/AutoSuggest?". caConcatGetParams($va_get_params)
		);


		$va_content = @json_decode($vs_content, true);

		if(!is_array($va_content) || !isset($va_content['result']) || !is_array($va_content['result']) || !sizeof($va_content['result'])) { return array(); }

		// the top two levels are 'result' and 'resume'
		$va_results = $va_content['result'];
		$va_return = array();

		foreach($va_results as $va_result) {
			// Skip non person names
			if($va_result["nametype"] != "personal") continue;

			$va_return['results'][] = array(
				'label' => $va_result['viafid'] . ' - '.$va_result['displayForm'],
				'url' => $vs_url."/".$va_result['viafid'],
				'idno' => $va_result['viafid'],
			);
		}

		return $va_return;
	}

	# ------------------------------------------------
	/**
	 * Fetch details about a specific item from a Iconclass-based data service for "more info" panel
	 *
	 * @param array $pa_settings Plugin settings values
	 * @param string $ps_url The URL originally returned by the data service uniquely identifying the item
	 * @return array An array of data from the data server defining the item.
	 */
	public function getExtendedInformation($pa_settings, $ps_url) {
		$vs_display = "<p><a href='$ps_url' target='_blank'>$ps_url</a></p>";

		return array('display' => $vs_display);
	}
	# ------------------------------------------------
}
