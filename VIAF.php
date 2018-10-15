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
$g_information_service_settings_Viaf = array();

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

		// We have a string, let's search it
		if($ps_search*1 == 0) {
		
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
			
		} else {
			// Otherwise it's a VIAF ID
			$vs_content = caQueryExternalWebservice(
				$vs_url_search = $vs_url ."/". $ps_search."/viaf.json"
			);
	
			$va_content = @json_decode($vs_content, true);
			
			if(!is_array($va_content)) { return array(); }
			
			$label=$va_content["ns1:mainHeadings"]["ns1:data"][0]["ns1:text"];
			$va_return['results'][] = array(
				'label' => $ps_search . ' - '.$label,
				'url' => $vs_url."/".$ps_search,
				'idno' => $ps_search
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
		$xml_file = $ps_url."/viaf.xml";
		$xml = simplexml_load_file($xml_file);
		$birthDate = date_create((string) $xml->children('ns1', true)->birthDate[0]); 
		//$birthDate=$xml->xpath("//ns1:birthDate");
		$deathDate = date_create((string) $xml->children('ns1', true)->deathDate[0]); 
		//$deathDate=$xml->xpath("//ns1:deathDate");
		$vs_display = "<p>Dates : ".date_format($birthDate,"d/m/Y")."-".date_format($deathDate,"d/m/Y")."</p>";
		$vs_display .= "<p>XML : <a href='".$xml_file."'>".$xml_file."</a></p>";
		$vs_display .= "<p>VIAF : <a href='".$ps_url."'>".$ps_url."</a></p>";
		
		$vs_display .= ""; //"<p><a href='$ps_url' target='_blank'>$ps_url</a></p>";

		return array('display' => $vs_display);
	}
	# ------------------------------------------------
}
