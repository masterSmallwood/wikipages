<?php

require 'src/PageViewDownloader.php';
require 'src/PageViewsHeap.php';

class TopPagesGenerator
{
    protected const TOP_COUNT = 25;

    public function __construct($deniedDomains = [])
    {
        $this->denied = $deniedDomains;
        $this->downloader = new PageViewDownloader();
    }

    public function generate($date, $hour)
    {
        $resultFilename = $this->generateResultFilename($date, $hour);

        if (file_exists($resultFilename)) {
            echo "Result $resultFilename has already been generated\n";

            return $resultFilename;
        }

        // TODO will need to force download for current hour maybe
        $pageViewsFilename = $this->downloader->download($date, $hour);

        // Build top sites for a single file. Store items in the heap in format [page, view_count].
        $stream = gzopen($pageViewsFilename, 'rb');
        $domains = [];
        while ($row = fgets($stream)) {
            [$domain, $page, $views, $bytes] = explode(' ', $row);

            // Exclude from our result if domain/page are on the denylist
            if (isset($this->denied[$domain][$page])) continue;

            if (isset($domains[$domain])) {
                $heap = $domains[$domain];
                if ($heap->count() < self::TOP_COUNT) {
                    $heap->insert([$page, $views]);
                }

                else {
                    // heap is full. Only insert page if it has more views than top of min heap (in the top 25)
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

        if (!file_exists('results')) {
            mkdir('results', 0777, true);
        }

        $resultFileStream = fopen("results/$resultFilename", 'w');
        foreach($domains as $domain => $heap) {
            while ($heap->count() > 0) {
                [$page, $views] = $heap->extract();
                fwrite($resultFileStream, "$domain $page $views");
            }
        }
        fclose($resultFileStream);

        echo "Result generated successfully\n";
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