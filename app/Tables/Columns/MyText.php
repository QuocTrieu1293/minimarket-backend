<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;
use Illuminate\Contracts\View\View;

class MyText extends Column
{
    protected string $view = 'tables.columns.my-text';
    protected string $text;
    
    public function text(string $text) {
        $this->text = $text;
    }

    public function render() : View {
        Parent::render();
        return view($this->getView(),['text' => $this->text]);
    }
}
