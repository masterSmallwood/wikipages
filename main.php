<?php

require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;
use App\Application;
use App\Validators\ValidateCommandInput;
use App\Exceptions\InvalidInputException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('Wikipedia Top Pages')
    ->setHelp('This command retrieves the top 25 pages for every subdomain of Wikipedia. Domain/Page combos that are on the denylist are not included.')
    ->setVersion('1.0.0')
    ->addArgument('date', InputArgument::OPTIONAL, 'The start/query date (year-month-day). Current UTC date if omitted.')
    ->addArgument('hour', InputArgument::OPTIONAL, 'The start/query UTC hour (0-23). Current UTC hour if omitted.')
    ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, 'Date to end the query')
    ->addOption('end-hour', null, InputOption::VALUE_OPTIONAL, 'Hour to end the query')
    ->setCode(function (InputInterface $input, OutputInterface $output) {

        $date = $input->getArgument('date') ?? Carbon::now()->toDateString();
        $hour = $input->getArgument('hour') ?? Carbon::now()->hour;
        $endDate = $input->getOption('end-date') ?? $date;
        $endHour = $input->getOption('end-hour') ?? $hour;

        if (!ValidateCommandInput::date($date)
            || !ValidateCommandInput::date($endDate)
            || !ValidateCommandInput::hour($hour)
            || !ValidateCommandInput::hour($endHour)
        ) {
            throw new InvalidInputException('Please enter valid dates and hours');
        }

        $app = new Application($output);

        $app->run($date, $hour, $endDate, $endHour);
    })
    ->run();
