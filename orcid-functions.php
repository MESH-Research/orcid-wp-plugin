<?php
define( 'MY_PLUGIN_PATH', plugin_dir_path( __DIR__ ) );
define( 'ORCID_XSLT', MY_PLUGIN_PATH . 'orcid-data-all.xsl');
define( 'ORCID_SITE', 'https://pub.orcid.org/');
define( 'ORCID_API_VERSION', 'v2.0');

/**
 * download data from orcid.org
 *
 * @param string $orcid_id - ORCiD ID
 * @return string $orcid_xml
 */
function download_orcid_data($orcid_id){
    $orcid_link = ORCID_SITE . ORCID_API_VERSION . "/" . $orcid_id;

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

// for testing
//$orcidID = "0000-0003-0265-9119"; // Alan Munn
//$orcidID = "0000-0003-1822-3109";  // Bronson Hui
//$orcidID = "0000-0002-8143-2408"; // Scott Schopieray
//$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
//$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick
