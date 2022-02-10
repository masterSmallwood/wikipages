<?php

namespace App\Contracts;

interface DownloadsFiles
{
    public function download($date, $hour);
}