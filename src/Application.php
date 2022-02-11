<?php

namespace App;

use Symfony\Component\Console\Output\OutputInterface;

class Application
{
    protected OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Run the application to generate results. One result file is generated per hour in its own file.
     *
     * @param $startDate
     * @param $startHour
     * @param $endDate
     * @param $endHour
     * @return void
     */
    public function run($startDate, $startHour, $endDate, $endHour)
    {
        $this->downloadDenyList($this->output);

        $deniedPages = $this->getDeniedPages();

        (new TopPagesGenerator((new PageViewDownloader()), $deniedPages, TopPagesGenerator::TOP_COUNT, function($message) {
            $this->output->writeln($message);
        }))->generate($startDate, $startHour, $endDate, $endHour);
    }

    /**
     * Saved denied domains/pages in a map to use for later
     *
     * @return array
     */
    public function getDeniedPages() : array
    {
        $this->output->writeln("<info>Generating map for denied pages</info>");
        $deniedPages = [];
        $path = getenv("TEST") === "1" ? "tests/" . DenylistDownloader::FILENAME : DenylistDownloader::FILENAME;
        $deniedDomainsStream = gzopen($path, 'rb');
        while ($row = fgets($deniedDomainsStream)) {
            [$domain, $page] = explode(" ", $row);
            $deniedPages[$domain][$page] = true;
        }
        gzclose($deniedDomainsStream);

        return $deniedPages;
    }

    /**
     * @param $output
     * @return void
     */
    protected function downloadDenyList($output)
    {
        // Download file of denylisted domains/pages if we havent already
        if (!file_exists(DenylistDownloader::FILENAME)) {
            $output->writeln("<info>Downloading file of denied pages</info>");
            (new DenylistDownloader())->download();
        }
    }
}