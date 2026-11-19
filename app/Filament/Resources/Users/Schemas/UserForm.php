<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // name
                TextInput::make('name')
                    ->label('Name')
                    ->placeholder('Name')
                    ->required(),

                // email
                TextInput::make('email')
                    ->label('Email')
                    ->placeholder('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),

                // password
                TextInput::make('password')
                    ->label('Password')
                    ->placeholder('Password')
                    ->dehydrateStateUsing(
                        fn ($state) => filled($state) ? Hash::make($state) : null
                    )
                    ->dehydrated(fn ($state) => filled($state))
                    ->password(),
            ]);
    }
}