<?php

namespace App;

use App\Contracts\DownloadsFiles;
use App\Exceptions\DownloadFailedException;

class PageViewDownloader implements DownloadsFiles
{
    protected const URL_BASE = 'https://dumps.wikimedia.org/other/pageviews/';

    /**
     * Create the filename if it doesn't exist already
     *
     * @param string $date Formatted year-month-day
     * @param string $hour 24-hour format. Enter value 0-23
     * @return string Return the filename
     */
    public function download($date, $hour) : string
    {
        $parsedDate = date_parse($date);

        $day = $parsedDate['day'] < 10 ? "0" . $parsedDate['day'] : (string)$parsedDate['day'];
        $month = $parsedDate['month'] < 10 ? "0" . $parsedDate['month'] : (string)$parsedDate['month'];
        $year = (string)$parsedDate['year'];
        $hour = $hour < 10 ? "0" . $hour : $hour;

        $url = self::URL_BASE . "$year/$year-$month/pageviews-$year$month$day-$hour" . '0000.gz';
        $filename = "$year-$month-$day-$hour";
        if (!file_exists($filename)) {
            echo "Downloading views from $date at hour $hour\n";
            $content = file_get_contents($url);
            if ($content === false) {
                throw new DownloadFailedException();
            }
            file_put_contents($filename, $content);
        }

        return $filename;
    }
}