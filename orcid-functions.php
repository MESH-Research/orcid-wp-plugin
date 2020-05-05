<?php
define( 'MY_PLUGIN_PATH', plugin_dir_path( __DIR__ ) );
define( 'ORCID_XSLT', MY_PLUGIN_PATH . 'orcid-data-works.xsl');
define( 'ORCID_SITE', 'https://pub.orcid.org/');
define( 'ORCID_API_VERSION', 'v2.0/');

/*
 * download data from orcid.org
 */
function download_orcid_data($orcid_id){
    $orcidLink = ORCID_SITE . ORCID_API_VERSION . $orcid_id;

    $ch = curl_init() or exit("failed curl_init()");
    // curl_setopt($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $orcidLink);

    $orcid_xml = curl_exec($ch) or exit("unable to run curl_exec() with url ($orcidLink) ");
    curl_close($ch);

    return $orcid_xml;
}

/*
 * format the orcid XML into HTML with XSLT
 */
function format_orcid_data_as_html($orcid_xml){
    $xmlDoc = new DOMDocument();
    $xmlDoc->loadXML($orcid_xml);

    $xslDoc = new DOMDocument();
    $xslDoc->load(ORCID_XSLT);

    $htmlDoc = new XSLTProcessor();

    $htmlDoc->setParameter('', 'display-personal', 'no');
    $htmlDoc->importStylesheet($xslDoc);
    $orcid_html =  $htmlDoc->transformToXML($xmlDoc);

    return $orcid_html;
}
/*
 * (OBSOLETE) wrapper function
 */
function get_orcid_data($orcid_id)
{
    // get data from orcid.org
    $orcid_xml = download_orcid_data($orcid_id);
    // format as xml
    $orcid_html = format_orcid_data_as_html($orcid_xml);
    return $orcid_html;
}

?>
<?php
/*
 * for testing
 */

/*
$orcidID = "0000-0003-0265-9119"; // Alan Munn
//$orcidID = "0000-0003-1822-3109";  // Bronson Hui
//$orcidID = "0000-0002-8143-2408"; // Scott Schopieray
//$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
//$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick

//get_orcid_data($orcidID);
$orcid_xml = download_orcid_data($orcidID);
$orcid_html = format_orcid_data_as_html($orcid_xml);
echo $orcid_html;
*/

?>
