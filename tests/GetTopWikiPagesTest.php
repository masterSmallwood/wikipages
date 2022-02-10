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

        $topPagesGenerator = new TopPagesGenerator($pageViewsDownloader, deniedDomains: [], topCount: 2);
        $topPagesGenerator->generate('2022-02-01', 12);

        $content = file_get_contents('results/result_2022-02-01_12');
        echo $content;

        unlink('results/result_2022-02-01_12');
    }

    public function testWillNotIncludePagesOnDomainDenyList()
    {
        // TODO
    }
}