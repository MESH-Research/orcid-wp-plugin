<?php
define( 'MY_PLUGIN_PATH', plugin_dir_path( __DIR__ ) );
define( 'ORCID_XSLT', MY_PLUGIN_PATH . 'xsl/orcid-data-works.xsl');

function get_orcid_data($orcidID)
{
    $orcidLink = "https://pub.orcid.org/v2.0/" . $orcidID;

    $ch = curl_init() or exit("failed curl_init()");
    // curl_setopt($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $orcidLink);

    $result = curl_exec($ch) or exit("unable to run curl_exec() with url ($orcidLink) ");
    curl_close($ch);

    $xmlDoc = new DOMDocument();
    $xmlDoc->loadXML($result);

    $xslDoc = new DOMDocument();
    $xslDoc->load(ORCID_XSLT);

    $htmlDoc = new XSLTProcessor();

    $htmlDoc->setParameter('', 'display-personal', 'no');
    $htmlDoc->importStylesheet($xslDoc);
    return $htmlDoc->transformToXML($xmlDoc);

    
}

?>
<?php
/**
$orcidID = "0000-0003-0265-9119"; // Alan Munn
//$orcidID = "0000-0003-1822-3109";  // Bronson Hui
//$orcidID = "0000-0002-8143-2408"; // Scott Schopieray
//$orcidID = "0000-0003-3953-7940"; // Chris Long (U of CO at Boulder)
//$orcidID = "0000-0002-5251-0307"; // Kathleen Fitzpatrick

get_orcid_data($orcidID);
*/

?>
