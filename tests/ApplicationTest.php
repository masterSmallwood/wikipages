<?php

use App\Application;
use App\DenylistDownloader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class ApplicationTest extends TestCase
{
    public function testCanGetDeniedPagesFromDownloadedFile(): void
    {
        $app = new Application((new ConsoleOutput()));
        file_put_contents('tests/' . DenylistDownloader::FILENAME, 'abc page1');
        $deniedPages = $app->getDeniedPages();

        $this->assertEquals(1, count(array_keys($deniedPages)));
        $this->assertTrue(isset($deniedPages['abc']['page1']));
    }
}