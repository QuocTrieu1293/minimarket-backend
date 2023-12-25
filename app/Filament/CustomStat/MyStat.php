<?php 
namespace App\Filament\CustomStat;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Contracts\View\View;

class MyStat extends Stat {

    public function render(): View
    {
        return view('widget-statover.my-widget', $this->data());
    }

}


?>