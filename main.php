<?php

require 'src/DenylistDownloader.php';
require 'src/TopPagesGenerator.php';

// get passed in arguments
if (count($argv) < 3) {
    echo 'Provide a date and time';
    return;
}
$date = $argv[1];
$hour = $argv[2];

// Download file of denylisted domains/pages if we havent already
if (!file_exists(DenylistDownloader::FILENAME)) {
    echo "Downloading denylist file...\n";
    (new DenylistDownloader())->download();
}

// Saved denied domains/pages in a map to use for later
$deniedDomains = [];
$deniedDomainsStream = gzopen(DenylistDownloader::FILENAME, 'rb');
while ($row = fgets($deniedDomainsStream)) {
    [$domain, $page] = $row;
    $deniedDomains[$domain][$page] = true;
}
gzclose($deniedDomainsStream);

// generate result file for top pages
$topPagesGenerator = new TopPagesGenerator($deniedDomains);
$topPagesGenerator->generate($date, $hour);