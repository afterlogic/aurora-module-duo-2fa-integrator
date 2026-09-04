<?php

require_once __DIR__ . '/../../system/autoload.php';

\Aurora\System\Api::Init();

$aAllowedParams = array('duo_code', 'state', 'error', 'error_description');
$aFiltered = array();
foreach ($aAllowedParams as $sKey) {
    if (isset($_GET[$sKey])) {
        $aFiltered[$sKey] = $_GET[$sKey];
    }
}

\Aurora\System\Api::Location('../../?duo-callback' . ($aFiltered ? '&' . http_build_query($aFiltered) : ''));
