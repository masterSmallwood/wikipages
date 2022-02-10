<?php

namespace App;

use App\Contracts\DownloadsFiles;

class TopPagesGenerator
{
    protected const TOP_COUNT = 25;

    protected array $denied;

    protected DownloadsFiles $downloader;

    protected int $topCount;

    public function __construct(DownloadsFiles $downloader, $deniedDomains = [], $topCount = self::TOP_COUNT)
    {
        $this->denied = $deniedDomains;
        $this->downloader = $downloader;
        $this->topCount = $topCount;
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

        if (!file_exists('results')) {
            mkdir('results', 0777, true);
        }

        $resultFileStream = fopen("results/$resultFilename", 'w');
        foreach($domains as $domain => $heap) {
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

        echo "Result $resultFilename generated successfully\n";
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