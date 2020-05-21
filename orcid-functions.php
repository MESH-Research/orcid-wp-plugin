<?php
include_once( plugin_dir_path( __FILE__ ) . 'config.php');

/**
 * download data from orcid.org
 *
 * @param string $orcid_id - ORCiD ID
 * @return string $orcid_xml
 */
function download_orcid_data($orcid_id){
    $orcid_link = ORCID_URL . $orcid_id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $orcid_link);

    try {
        $orcid_xml = curl_exec($ch);
    } catch (Exception $e) {
        throw new Exception($e);
    }
    curl_close($ch);

    return $orcid_xml;
}

/**
 * format the orcid XML into HTML with XSLT
 *
 * parameters:
 * @param string $orcid_xml - XML as string
 * @param array $display_sections - which sections of orcid data to display
 * @return string orcid_html
 */
function format_orcid_data_as_html($orcid_xml, $display_sections){
    $xml_doc = new DOMDocument();
    $xml_doc->loadXML($orcid_xml);

    $xsl_doc = new DOMDocument();
    $xsl_doc->load(ORCID_XSLT);

    $html_doc = new XSLTProcessor();

    //
    // control which sections are displayed
	$html_doc->setParameter('', 'display_header', $display_sections['display_header']);
	// $html_doc->setParameter('', 'display_header', 'yes');
	$html_doc->setParameter('', 'display_personal', $display_sections['display_personal']);
    $html_doc->setParameter('', 'display_education', $display_sections['display_education']);
    $html_doc->setParameter('', 'display_employment', $display_sections['display_employment']);
    $html_doc->setParameter('', 'display_works', $display_sections['display_works']);
    $html_doc->setParameter('', 'display_fundings', $display_sections['display_fundings']);
    $html_doc->setParameter('', 'display_peer_reviews', $display_sections['display_peer_reviews']);

    $html_doc->importStylesheet($xsl_doc);
    $orcid_html =  $html_doc->transformToXML($xml_doc);

    return $orcid_html;
}

/**
 * Call back function for shortword [orcid-data section="section_name"]
 *
 * parameters:
 * @param array $atts - contains the name of the section to display. if none is specified the header line is displayed
 * @return string shortcode value
 *
 */
function orcid_data_function($atts) {
	//
	// which section of the orcid data to display.
	//if no section is specified display the header by default
	extract(shortcode_atts(array('section' => 'header',), $atts));
	//
	// now we want to display the *author's* (not the viewer's) data

	//
	// get the author's WordPress user id
	$author = get_the_author_meta('ID', false);

	//
	// get orcid data
	//
	// we can either download the data from orcid.org OR use the cached value
	// we download the data IFF ($download_from_orcid_flag = TRUE)
	// 1) there is no cached xml data
	// 2) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
	//
	$download_from_orcid_flag = FALSE;
	//
	// 2) there is no cached xml data
	if(empty(get_user_meta($author, '_orcid_xml', TRUE))){
		$download_from_orcid_flag = TRUE;
	}
	//
	// 3) the cached value is older than ORCID_CACHE_TIMEOUT (in seconds)
	$current_time = time();
	// last download time
	$orcid_xml_download_time = intval(get_user_meta($author, '_orcid_xml_download_time', TRUE));
	//
	$time_diff = $current_time - $orcid_xml_download_time;
	if($time_diff >= ORCID_CACHE_TIMEOUT){
		$download_from_orcid_flag = TRUE;
	}

	if($download_from_orcid_flag) {
		// return '<p>Downloading XML data from orcid.org</p>' . PHP_EOL;
		$orcid_id = get_user_meta($author, '_orcid_id', TRUE);
		$orcid_xml = download_orcid_data($orcid_id);
		update_user_meta($author, '_orcid_xml', $orcid_xml);
		//
		// keep track of when download occurred
		update_user_meta($author, '_orcid_xml_download_time', strval(time()));
	} else {
		// return '<p>Using cached XML data</p>' . PHP_EOL;
		// return '<p>author WP id = ' . intval($author) . '</p>';
		// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$orcid_xml = get_user_meta($author, '_orcid_xml', TRUE);
	}
	// $orcid_xml = get_user_meta($author, '_orcid_xml', TRUE);


	//
	// determine which section to display
	if ($section == 'header') {
		$display_sections['display_header'] = 'yes';
	} else {
		$display_sections['display_header'] = 'no';
	}
	if ($section == 'personal') {
		$display_sections['display_personal'] = 'yes';
	} else {
		$display_sections['display_personal'] = 'no';
	}
	if ($section == 'education') {
		$display_sections['display_education'] = 'yes';
	} else {
		$display_sections['display_education'] = 'no';
	}
	if ($section == 'employment') {
		$display_sections['display_employment'] = 'yes';
	} else {
		$display_sections['display_employment'] = 'no';
	}
	if ($section == 'works') {
		$display_sections['display_works'] = 'yes';
	} else {
		$display_sections['display_works'] = 'no';
	}
	if ($section == 'fundings') {
		$display_sections['display_fundings'] = 'yes';
	} else {
		$display_sections['display_fundings'] = 'no';
	}
	if ($section == 'peer_reviews') {
		$display_sections['display_peer_reviews'] = 'yes';
	} else {
		$display_sections['display_peer_reviews'] = 'no';
	}
	//
	// format as HTML
	$orcid_html = format_orcid_data_as_html($orcid_xml, $display_sections);
	$return_string = $orcid_html;

	return $return_string;
}

// for testing
//$orcidID = "0000-0003-0265-9119"; // Alan Munn
//$orcidID = "0000-0003-1822-3109";  // Bronson Hui
//$orcidID = "0000-0002-8143-2408"; // Scott Schopieray
//$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
//$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick
