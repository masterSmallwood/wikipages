<?php

class PageViewsHeap extends SplMinHeap {
    public function compare(mixed $value1, mixed $value2)
    {
        if ($value1[1] < $value2[1]) return -1;
        if ($value1[1] === $value2[1]) return 0;
        return 1;
    }
}

$url = "https://dumps.wikimedia.org/other/pageviews/2022/2022-01/pageviews-20220101-000000.gz";

//TODO download and save blacklisted domains

// Download GZ file. Delete it when were done with it.
if (file_put_contents('file_name', file_get_contents($url)))
{
    echo "File downloaded successfully";
}

$stream = gzopen('file_name', 'rb');

$domains = [];
$limit = 0;
while ($row = fgets($stream)) {
//    if ($limit >= 1000) break;

    [$domain, $page, $views, $bytes] = explode(' ', $row);

    if (isset($domains[$domain])) {
        $heap = $domains[$domain];
        // if heap isnt full, insert
        if ($heap->count() < 25) {
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


    $limit++;
}

// print out top 25 sites for each domain
foreach($domains as $domain => $heap) {
    echo "Top 25 pages for $domain are:\n";
    while ($heap->count() > 0) {
        [$page, $views] = $heap->extract();
        echo "$page, $views\n";
    }
    echo "\n";
}
echo "there were a total of " . count(array_keys($domains)) . " domains \n";

gzclose($stream);