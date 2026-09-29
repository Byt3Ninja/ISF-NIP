<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavigationPills extends Component
{
    public bool $card;
    public array $tabs;

    public function __construct(array $tabs, bool $card = true)
    {
        $this->card = $card;
        $this->tabs = $tabs;
    }

    public function render(): View|Closure|string
    {
        return view('components.navigation-pills');
    }
}
