<?php
$file = dirname(dirname(__FILE__)) . "/tmp/templateversions.json";

//setup defaults
$versions = array();
$versions['html'] = false;
$versions['dep'] = false;

//Get the versions from github.
$html = @file_get_contents('https://raw.github.com/unl/wdntemplates/master/VERSION_HTML');
$dep = @file_get_contents('https://raw.github.com/unl/wdntemplates/master/VERSION_DEP');

preg_match('/([0-9.]*)/', $html, $matches);

if (isset($matches[1])) {
    $versions['html'] = $matches[1];
}

unset($matches);

preg_match('/([0-9.]*)/', $dep, $matches);

if (isset($matches[1])) {
    $versions['dep'] = $matches[1];
}

$versions = json_encode($versions);

file_put_contents($file, $versions);