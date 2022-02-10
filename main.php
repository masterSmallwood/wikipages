<?php

require __DIR__.'/vendor/autoload.php';

use App\TopPagesGenerator;
use App\DenylistDownloader;
use App\PageViewDownloader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('My Super Command')
    ->setHelp('This command retrieves the top 25 pages for every subdomain of Wikipedia. Domain/Page combos that are on the denylist are not included.')
    ->setVersion('1.0.0')
    ->addArgument('date', InputArgument::REQUIRED, 'The date (year-month-day)')
    ->addArgument('hour', InputArgument::REQUIRED, 'The hour (0-23)')
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        // TODO maybe validate these
        $date = $input->getArgument('date');
        $hour = $input->getArgument('hour');

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

        // generate result file for top pages based on provided date and hour
        $topPagesGenerator = new TopPagesGenerator((new PageViewDownloader()), $deniedDomains);
        $topPagesGenerator->generate($date, $hour);

        //TODO handle date-hour range
        // for each hour in range, run the above thing?
    })
    ->run();
