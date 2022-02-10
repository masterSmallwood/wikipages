<?php

class DenylistDownloader
{
    public const FILENAME = 'denylist';

    public const DENYLIST_URL = 'https://s3.amazonaws.com/dd-interview-data/data_engineer/wikipedia/blacklist_domains_and_pages';

    /**
     * Download and save blacklisted domains
     *
     * @return false|int
     */
    public function download()
    {
        return file_put_contents(self::FILENAME, file_get_contents(self::DENYLIST_URL));
    }
}