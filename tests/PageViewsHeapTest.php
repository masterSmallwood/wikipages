<?php

use PHPUnit\Framework\TestCase;

class PageViewsHeapTest extends TestCase
{
    public function testWillStoreDomainWithLeastPageViewsAtTopOfHeap(): void
    {
        $heap = new \App\PageViewsHeap();
        $heap->insert(['page1', 5]);
        $heap->insert(['page2', 3]);

        $this->assertEquals(3, $heap->top()[1]);
    }
}