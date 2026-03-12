<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\PaymentTransaction;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('payments.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('payments.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('payments.navigation_group');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('payments.label'))
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->schema([
                        Components\TextInput::make('uuid')
                            ->label(__('payments.uuid'))
                            ->disabled(),

                        Components\TextInput::make('status')
                            ->label(__('payments.status'))
                            ->disabled(),

                        Components\TextInput::make('amount')
                            ->label(__('payments.amount'))
                            ->disabled(),

                        Components\TextInput::make('currency')
                            ->label(__('payments.currency'))
                            ->disabled(),

                        Components\TextInput::make('provider')
                            ->label(__('payments.provider'))
                            ->disabled(),

                        Components\TextInput::make('payment_method')
                            ->label(__('payments.payment_method'))
                            ->disabled(),

                        Components\TextInput::make('phone_number')
                            ->label(__('payments.phone_number'))
                            ->disabled(),

                        Components\DateTimePicker::make('paid_at')
                            ->label(__('payments.paid_at'))
                            ->disabled(),

                        Components\DateTimePicker::make('failed_at')
                            ->label(__('payments.failed_at'))
                            ->disabled()
                            ->hidden(fn ($get): bool => empty($get('failed_at'))),

                        Components\Textarea::make('failure_reason')
                            ->label(__('payments.failure_reason'))
                            ->disabled()
                            ->columnSpanFull()
                            ->hidden(fn ($get): bool => empty($get('failure_reason'))),

                        Components\KeyValue::make('provider_metadata')
                            ->label(__('payments.provider_metadata'))
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label(__('payments.uuid'))
                    ->searchable()
                    ->copyable()
                    ->limit(12),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('payments.amount'))
                    ->formatStateUsing(fn ($state, $record): string => number_format((float) $state, 2, ',', ' ').' '.$record->currency)
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->label(__('payments.provider'))
                    ->formatStateUsing(fn (string $state): string|array => __("payments.providers.{$state}") ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('payments.payment_method'))
                    ->formatStateUsing(fn (?string $state): string|array => $state ? (__("payments.methods.{$state}") ?? $state) : '-')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_voided')
                    ->label(__('payments.voided'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->adjustments()->where('type', 'void')->exists())
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('payments.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string|array => __("payments.statuses.{$state}") ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending', 'processing' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.full_name')
                    ->label(__('payments.member'))
                    ->default('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('payments.paid_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('payments.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('payments.status'))
                    ->options([
                        'pending' => __('payments.statuses.pending'),
                        'processing' => __('payments.statuses.processing'),
                        'completed' => __('payments.statuses.completed'),
                        'failed' => __('payments.statuses.failed'),
                        'refunded' => __('payments.statuses.refunded'),
                        'cancelled' => __('payments.statuses.cancelled'),
                    ]),

                Tables\Filters\SelectFilter::make('provider')
                    ->label(__('payments.provider'))
                    ->options([
                        'cinetpay' => __('payments.providers.cinetpay'),
                        'stripe' => __('payments.providers.stripe'),
                    ]),

                Tables\Filters\SelectFilter::make('campus_id')
                    ->label(__('payments.campus'))
                    ->relationship('campus', 'name'),
            ])
            ->actions([
                Actions\Action::make('void')
                    ->label(__('payments.void'))
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('payments.void_heading'))
                    ->modalDescription(__('payments.void_description'))
                    ->form([
                        Components\TextInput::make('reason')
                            ->label(__('payments.void_reason'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (PaymentTransaction $record, array $data): void {
                        $record->adjustments()->create([
                            'tenant_id' => $record->tenant_id,
                            'user_id' => auth()->id(),
                            'type' => 'void',
                            'amount_before' => $record->amount,
                            'amount_after' => 0,
                            'reason' => $data['reason'],
                        ]);

                        if ($record->status !== 'completed') {
                            $record->update(['status' => 'cancelled']);
                        }
                    })
                    ->visible(fn (PaymentTransaction $record): bool => ! $record->adjustments()->where('type', 'void')->exists()),
                Actions\ViewAction::make()
                    ->iconButton(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
        ];
    }
}
