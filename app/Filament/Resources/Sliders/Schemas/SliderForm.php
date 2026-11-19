<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->label('Slider Image')
                    ->placeholder('Slider Image')
                    ->required(),

                TextInput::make('link')
                    ->label('Link')
                    ->placeholder('Link')
                    ->required(),
            ]);
    }
}