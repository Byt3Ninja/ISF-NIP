<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CircularProgress extends Component
{
    public $size;
    public $color;

    public function __construct($size, $color)
    {
        $this->size = $size;
        $this->color = $color;
    }

    public function render(): View|Closure|string
    {
        return view('components.circular-progress');
    }
}