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

        $topPagesGenerator = Mockery::mock(TopPagesGenerator::class, [$pageViewsDownloader, [], 2, fn($a) => ($a)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('deletePageViewsFile')
            ->andReturnNull()
            ->getMock();

        $startDate = '2022-02-01';
        $startHour = 12;
        $topPagesGenerator->generate($startDate, $startHour, $startDate, $startHour);

        $content = file_get_contents('tests/results/result_2022-02-01_12');
        $rows = explode("\n", $content);

        $this->assertEquals('subdomain1 page3 3', $rows[0]);
        $this->assertEquals('subdomain1 page2 2', $rows[1]);
        $this->assertEquals('subdomain2 page6 6', $rows[2]);
        $this->assertEquals('subdomain2 page5 5', $rows[3]);

        unlink('tests/results/result_2022-02-01_12');
    }

    public function testWillNotIncludePagesOnDomainDenyList()
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldReceive('download')->andReturn('tests/wikiPageCounts');

        $deniedPages = ['subdomain1' => ['page3' => true], 'subdomain2' => ['page6' => true]];
        $topPagesGenerator = Mockery::mock(TopPagesGenerator::class, [$pageViewsDownloader, $deniedPages, 2, fn($a) => ($a)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('deletePageViewsFile')
            ->andReturnNull()
            ->getMock();

        $startDate = '2022-02-01';
        $startHour = 12;
        $topPagesGenerator->generate($startDate, $startHour, $startDate, $startHour);

        $content = file_get_contents('tests/results/result_2022-02-01_12');
        $rows = explode("\n", $content);

        $this->assertEquals('subdomain1 page2 2', $rows[0]);
        $this->assertEquals('subdomain1 page1 1', $rows[1]);
        $this->assertEquals('subdomain2 page5 5', $rows[2]);
        $this->assertEquals('subdomain2 page4 4', $rows[3]);

        unlink('tests/results/result_2022-02-01_12');
    }

    public function testWillNotRecomputeResults(): void
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldNotReceive('download');

        $topPagesGenerator = Mockery::mock(TopPagesGenerator::class, [$pageViewsDownloader, [], 2, fn($a) => ($a)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        file_put_contents('tests/results/result_2022-02-01_12', 'test data');
        $startDate = '2022-02-01';
        $startHour = 12;
        $topPagesGenerator->generate($startDate, $startHour, $startDate, $startHour);

        $this->expectOutputString("Result result_2022-02-01_12 has already been generated\n");

        unlink('tests/results/result_2022-02-01_12');
    }

    public function testWillGenerateResultsForARange()
    {
        $pageViewsDownloader = Mockery::mock(PageViewDownloader::class);
        $pageViewsDownloader->shouldReceive('download')->andReturn('tests/wikiPageCounts');

        $startDate = '2022-02-01';
        $startHour = 12;
        $endDate = '2022-02-01';
        $endHour = 13;

        $topPagesGenerator = Mockery::mock(TopPagesGenerator::class, [$pageViewsDownloader, [], 2, fn($a) => ($a)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('deletePageViewsFile')
            ->andReturnNull()
            ->getMock();

        $topPagesGenerator->generate($startDate, $startHour, $endDate, $endHour);

        $content1 = file_get_contents('tests/results/result_2022-02-01_12');
        $content2 = file_get_contents('tests/results/result_2022-02-01_13');

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

        unlink('tests/results/result_2022-02-01_12');
        unlink('tests/results/result_2022-02-01_13');
    }
}