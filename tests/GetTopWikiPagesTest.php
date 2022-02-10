<?php

use App\TopPagesGenerator;
use App\PageViewDownloader;
use PHPUnit\Framework\TestCase;

class GetTopWikiPagesTest extends TestCase
{
    public function testCanGetTopPagesForEachDomain(): void
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldReceive('download')->andReturn('tests/wikiPageCounts');

        $startDate = '2022-02-01';
        $startHour = 12;
        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedPages: [], topCount: 2, outputCallback: fn($a) => ($a));
        $topPagesGenerator->generate($startDate, $startHour, $startDate, $startHour);

        $content = file_get_contents('results/result_2022-02-01_12');
        $rows = explode("\n", $content);

        $this->assertEquals('subdomain1 page3 3', $rows[0]);
        $this->assertEquals('subdomain1 page2 2', $rows[1]);
        $this->assertEquals('subdomain2 page6 6', $rows[2]);
        $this->assertEquals('subdomain2 page5 5', $rows[3]);

        unlink('results/result_2022-02-01_12');
    }

    public function testWillNotIncludePagesOnDomainDenyList()
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldReceive('download')->andReturn('tests/wikiPageCounts');

        $startDate = '2022-02-01';
        $startHour = 12;
        $deniedPages = ['subdomain1' => ['page3' => true], 'subdomain2' => ['page6' => true]];
        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedPages: $deniedPages, topCount: 2, outputCallback: fn($a) => ($a));
        $topPagesGenerator->generate($startDate, $startHour, $startDate, $startHour);

        $content = file_get_contents('results/result_2022-02-01_12');
        $rows = explode("\n", $content);

        $this->assertEquals('subdomain1 page2 2', $rows[0]);
        $this->assertEquals('subdomain1 page1 1', $rows[1]);
        $this->assertEquals('subdomain2 page5 5', $rows[2]);
        $this->assertEquals('subdomain2 page4 4', $rows[3]);

        unlink('results/result_2022-02-01_12');
    }

    public function testWillGenerateResultsForARange()
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldReceive('download')->andReturn('tests/wikiPageCounts');

        $startDate = '2022-02-01';
        $startHour = 12;
        $endDate = '2022-02-01';
        $endHour = 13;
        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedPages: [], topCount: 2, outputCallback: fn($a) => ($a));
        $topPagesGenerator->generate($startDate, $startHour, $endDate, $endHour);

        $content1 = file_get_contents('results/result_2022-02-01_12');
        $content2 = file_get_contents('results/result_2022-02-01_13');

        $rows1 = explode("\n", $content1);
        $this->assertEquals('subdomain1 page3 3', $rows1[0]);
        $this->assertEquals('subdomain1 page2 2', $rows1[1]);
        $this->assertEquals('subdomain2 page6 6', $rows1[2]);
        $this->assertEquals('subdomain2 page5 5', $rows1[3]);

        $rows2 = explode("\n", $content2);
        $this->assertEquals('subdomain1 page3 3', $rows2[0]);
        $this->assertEquals('subdomain1 page2 2', $rows2[1]);
        $this->assertEquals('subdomain2 page6 6', $rows2[2]);
        $this->assertEquals('subdomain2 page5 5', $rows2[3]);

        unlink('results/result_2022-02-01_12');
        unlink('results/result_2022-02-01_13');
    }
}