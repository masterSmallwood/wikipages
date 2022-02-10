<?php

require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;
use App\TopPagesGenerator;
use App\DenylistDownloader;
use App\PageViewDownloader;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('Wikipedia Top Pages')
    ->setHelp('This command retrieves the top 25 pages for every subdomain of Wikipedia. Domain/Page combos that are on the denylist are not included.')
    ->setVersion('1.0.0')
//    ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Date to start the query. If end-date option not provided, then only query this day')
//    ->addOption('hour', null, InputOption::VALUE_REQUIRED, 'Hour to start the query. If end-hour option not provided, then only query this hour')
    ->addArgument('date', InputArgument::REQUIRED, 'The start date (year-month-day)')
    ->addArgument('hour', InputArgument::REQUIRED, 'The start hour (0-23)')
    ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, 'Date to end the query')
    ->addOption('end-hour', null, InputOption::VALUE_OPTIONAL, 'Hour to end the query')
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        // TODO maybe validate these
        $date = $input->getArgument('date');
        $hour = $input->getArgument('hour');
        $endDate = $input->getOption('end-date') ?? $date;
        $endHour = $input->getOption('end-hour') ?? $hour;

        // Download file of denylisted domains/pages if we havent already
        if (!file_exists(DenylistDownloader::FILENAME)) {
            $output->writeln("<info>Downloading file of denied pages</info>");
            (new DenylistDownloader())->download();
        }

        // Saved denied domains/pages in a map to use for later
        $output->writeln("<info>Generating map for denied pages</info>");
        $deniedPages = [];
        $deniedDomainsStream = gzopen(DenylistDownloader::FILENAME, 'rb');
        while ($row = fgets($deniedDomainsStream)) {
            [$domain, $page] = $row;
            $deniedPages[$domain][$page] = true;
        }
        gzclose($deniedDomainsStream);

        // Generate results for query range
        (new TopPagesGenerator((new PageViewDownloader()), $deniedPages, TopPagesGenerator::TOP_COUNT, function($message) use($output) {
            $output->writeln($message);
        }))->generate($date, $hour, $endDate, $endHour);

    })
    ->run();
