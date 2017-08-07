<?php
// Get all dependencies
require_once dirname(__DIR__).'/../../config/init_libs.php';
foreach (glob(ROOTDIR.'/app/modules/*/interfaces/*.php') as $require_path) {
    require_once $require_path;
}
foreach (glob(ROOTDIR.'/app/modules/*/classes/*.php') as $require_path) {
    require_once $require_path;
}
foreach (glob(ROOTDIR.'/support/addresses_autoupdate/classes/*.php') as $require_path) {
    require_once $require_path;
}

// Constants
const STORAGE_PATH = ROOTDIR.'/support/addresses_autoupdate/storage';
if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH);
    chmod(STORAGE_PATH, 0775);
}
const TMP_PATH = ROOTDIR.'/support/addresses_autoupdate/tmp';
if (!is_dir(TMP_PATH)) {
    mkdir(TMP_PATH);
    chmod(TMP_PATH, 0775);
}
const TMP_DOWNLOAD_PATH = ROOTDIR.'/support/addresses_autoupdate/tmp/download';
if (!is_dir(TMP_DOWNLOAD_PATH)) {
    mkdir(TMP_DOWNLOAD_PATH);
    chmod(TMP_DOWNLOAD_PATH, 0775);
}
const SOURCES_XML_PATH = ROOTDIR.'/support/addresses_autoupdate/sources.xml';
const DEBUG = true;  // Change to false to stop the output

// Main body
$converter = DataConverter::getInstance();
$sources_array = $converter->XMLToArray(file_get_contents(SOURCES_XML_PATH));
// var_dump($sources_array);
foreach ($sources_array['country'] as $country) {
    AutoUpdater::update($country['name'], $country['url']);
}
if (DEBUG) echo "Address system was updated!".PHP_EOL;