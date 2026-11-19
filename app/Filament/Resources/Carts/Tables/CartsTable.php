<?php

namespace App\Filament\Resources\Carts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.title')
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->searchable(),

                TextColumn::make('qty'),

                TextColumn::make('total')
                    ->money('IDR', locale: 'id')
                    ->getStateUsing(
                        fn ($record) =>
                            $record->qty * $record->product->price
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}