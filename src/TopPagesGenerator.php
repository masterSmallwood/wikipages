<?php

namespace App;

use Carbon\Carbon;
use App\Contracts\DownloadsFiles;

class TopPagesGenerator
{
    public const TOP_COUNT = 25;

    protected array $denied;

    protected DownloadsFiles $downloader;

    protected int $topCount;

    protected mixed $output;

    protected string $resultDir;

    /**
     * @param DownloadsFiles $downloader
     * @param $deniedPages
     * @param $topCount
     * @param $outputCallback
     */
    public function __construct(DownloadsFiles $downloader, $deniedPages = [], $topCount = self::TOP_COUNT, $outputCallback = null)
    {
        $this->denied = $deniedPages;
        $this->downloader = $downloader;
        $this->topCount = $topCount;
        $this->output = $outputCallback;
        $this->resultDir = getenv("TEST") === "1" ? "tests/results/" : "results/";
    }

    /**
     * @param $date
     * @param $hour
     * @param $endDate
     * @param $endHour
     * @return array
     */
    public function generate($date, $hour, $endDate, $endHour) : array
    {
        // Generate results for query range
        $start = Carbon::parse($date)->setHour($hour);
        $end = Carbon::parse($endDate)->setHour($endHour);

        $resultFilenames = [];

        while ($start->lessThanOrEqualTo($end)) {
            $queryDate = $start->toDateString();
            $queryHour = $start->hour;

            // generate result file for top pages based on provided date and hour
            ($this->output)("<info>Generating result for top 25 pages per domain for date $queryDate and hour $queryHour</info>");

            $resultFilename = $this->generateResultFilename($queryDate, $queryHour);

            // Skip this query if we've already generated the results
            if (file_exists($this->resultDir . $resultFilename)) {
                echo "Result $resultFilename has already been generated\n";

                $resultFilenames[] = $resultFilename;
                $start->addHour();
                continue;
            }

            // TODO will need to force download for current hour maybe?
            $pageViewsFilename = $this->downloader->download($queryDate, $queryHour);

            // Build top sites for a single file. Store items in the heap in format [page, view_count].
            $stream = gzopen($pageViewsFilename, 'rb');
            $domains = [];
            while ($row = fgets($stream)) {
                [$domain, $page, $views, $bytes] = explode(' ', $row);

                // Exclude from our result if page is on the denylist
                if (isset($this->denied[$domain][$page])) continue;

                if (isset($domains[$domain])) {
                    $heap = $domains[$domain];
                    if ($heap->count() < $this->topCount) {
                        $heap->insert([$page, $views]);
                    }

                    else {
                        // heap is full. Only insert page if it has more views than top of min heap (in the top K)
                        if ($views > $heap->top()[1]) {
                            $heap->extract();
                            $heap->insert([$page, $views]);
                        }
                    }
                } else {
                    $domains[$domain] = new PageViewsHeap();
                    $domains[$domain]->insert([$page, $views]);
                }
            }

            $this->deletePageViewsFile($pageViewsFilename);

            if (!file_exists($this->resultDir)) {
                mkdir($this->resultDir, 0777, true);
            }

            $this->writeQueryResultsToFile($resultFilename, $domains);

            $start->addHour();
        }

        return $resultFilenames;
    }

    /**
     * Delete the raw downloaded file that's used to generate page views
     *
     * @param $filename
     * @return void
     */
    protected function deletePageViewsFile($filename)
    {
        unlink($filename);
    }

    /**
     * @param $resultFilename
     * @param $domainResults
     * @return void
     */
    protected function writeQueryResultsToFile($resultFilename, $domainResults)
    {
        // write results from query to a results file
        $resultFileStream = fopen($this->resultDir . $resultFilename, 'w');
        foreach($domainResults as $domain => $heap) {
            $results = [];
            while ($heap->count() > 0) {
                $results[] = $heap->extract();
            }
            foreach(array_reverse($results) as $result) {
                [$page, $views] = $result;
                fwrite($resultFileStream, "$domain $page $views\n");
            }
        }
        fclose($resultFileStream);

        ($this->output)("<info>Generated new result file: $resultFilename</info>\n");
    }

    /**
     * @param string $date
     * @param string|int $hour
     * @return string
     */
    protected function generateResultFilename(string $date, string|int $hour) : string
    {
        return "result_" . $date . "_" . $hour;
    }
}