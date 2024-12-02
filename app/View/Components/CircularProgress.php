<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CircularProgress extends Component
{
    public $size;
    public $color;
    public $progress;

    public function __construct($size, $color, $progress)
    {
        $this->size = $size;
        $this->color = $color;
        $this->progress = $progress;
    }

    public function render(): View|Closure|string
    {
        return view('components.circular-progress');
    }
}