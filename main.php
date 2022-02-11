<?php

require __DIR__.'/vendor/autoload.php';

use App\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('Wikipedia Top Pages')
    ->setHelp('This command retrieves the top 25 pages for every subdomain of Wikipedia. Domain/Page combos that are on the denylist are not included.')
    ->setVersion('1.0.0')
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

        $app = new Application($output);

        $app->run($date, $hour, $endDate, $endHour);
    })
    ->run();
