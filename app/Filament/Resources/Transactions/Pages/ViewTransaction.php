<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('General Information')
                    ->schema([
                        TextEntry::make('invoice')
                            ->label('Invoice'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'success' => 'success',
                                'expired' => 'gray',
                                'failed' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Customer Information')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Customer Name'),

                        TextEntry::make('customer.email')
                            ->label('Email Address'),

                        TextEntry::make('full_address')
                            ->label('Address'),
                    ])
                    ->columns(3),

                Section::make('Ongkos Kirim')
                    ->schema([
                        TextEntry::make('shipping.shipping_courier')
                            ->label('Jasa Kirim'),

                        TextEntry::make('shipping.shipping_service')
                            ->label('Layanan Kirim'),

                        TextEntry::make('shipping.shipping_cost')
                            ->label('Ongkos Kirim')
                            ->numeric(decimalPlaces: 0)
                            ->prefix('Rp '),
                    ])
                    ->columns(3),

                Section::make('Transaction Details')
                    ->schema([
                        RepeatableEntry::make('transactionDetails')
                            ->label('Items Details')
                            ->schema([
                                TextEntry::make('product.title')
                                    ->label('Product Name'),

                                TextEntry::make('qty')
                                    ->label('Quantity'),

                                TextEntry::make('price')
                                    ->label('Price')
                                    ->numeric(decimalPlaces: 0)
                                    ->prefix('Rp '),
                            ])
                            ->columns(3),
                    ]),

                Section::make()
                    ->schema([
                        TextEntry::make('total')
                            ->label('Grand Total')
                            ->numeric(decimalPlaces: 0)
                            ->prefix('Rp ')
                            ->weight('bold')
                            ->color('primary'),
                    ])
                    ->columns(1),
            ]);
    }
}