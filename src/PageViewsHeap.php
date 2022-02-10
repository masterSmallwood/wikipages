<?php

namespace App;

use SplMinHeap;

class PageViewsHeap extends SplMinHeap {
    public function compare(mixed $value1, mixed $value2)
    {
        if ($value1[1] < $value2[1]) return 1;
        if ($value1[1] === $value2[1]) return 0;
        return -1;
    }
}