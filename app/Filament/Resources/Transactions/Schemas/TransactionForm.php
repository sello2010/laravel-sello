<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('province_name')
                    ->required(),
                TextInput::make('city_name')
                    ->required(),
                TextInput::make('district_name')
                    ->required(),
                TextInput::make('subdistrict_name')
                    ->required(),
                TextInput::make('zip_code')
                    ->required()
                    ->numeric(),
                Textarea::make('full_address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('invoice')
                    ->required(),
                TextInput::make('weight')
                    ->required()
                    ->numeric(),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'success' => 'Success', 'expired' => 'Expired', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
