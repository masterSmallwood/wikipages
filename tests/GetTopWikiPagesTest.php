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

        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedPages: [], topCount: 2);
        $topPagesGenerator->generate('2022-02-01', 12);

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

        $deniedPages = ['subdomain1' => ['page3' => true], 'subdomain2' => ['page6' => true]];
        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedPages: $deniedPages, topCount: 2);
        $topPagesGenerator->generate('2022-02-01', 12);

        $content = file_get_contents('results/result_2022-02-01_12');
        $rows = explode("\n", $content);

        $this->assertEquals('subdomain1 page2 2', $rows[0]);
        $this->assertEquals('subdomain1 page1 1', $rows[1]);
        $this->assertEquals('subdomain2 page5 5', $rows[2]);
        $this->assertEquals('subdomain2 page4 4', $rows[3]);

        unlink('results/result_2022-02-01_12');
    }
}