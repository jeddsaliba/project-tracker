<?php

namespace App\Filament\Resources;

use App\Enums\NavGroup;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = NavGroup::UM->value;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->unique(ignoreRecord: true)
                                    ->required(),
                                \Ysfkaya\FilamentPhoneInput\Forms\PhoneInput::make('profile.phone')
                                    ->formatStateUsing(function (Model | null $record) {
                                        return $record?->profile?->phone;
                                    })
                                    ->required(),
                                Forms\Components\DatePicker::make('profile.birthdate')
                                    ->formatStateUsing(function (Model | null $record) {
                                        return $record?->profile ? Carbon::parse($record->profile->birthdate)->format('Y-m-d') : null;
                                    })
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active'),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Change Password')
                            ->hidden(fn () => !Auth::user()->isSuperAdmin())
                            ->label(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord ? 'Set Password' : 'Change Password')
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->confirmed()
                                    ->revealable()
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->password()
                                    ->revealable()
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                            ]),
                        Forms\Components\Tabs\Tab::make('Reset Password')
                            ->hidden(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->schema([
                                Forms\Components\Section::make()
                                    ->description('This will generate a random password and send it to the user\'s email address. Upon initial login, the user will be prompted to change the password.')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('resetPassword')
                                                ->label('Send password reset')
                                                ->requiresConfirmation()
                                                ->action(function (\Livewire\Component $livewire) {
                                                    $user = $livewire->getRecord();
                                                    $password = Str::random(8);
                                                    $user->save();
                                                    $user->notify(new ResetPasswordNotification($password));
                                                    Notification::make()
                                                        ->title('Your password was reset. Please check your email.')
                                                        ->success()
                                                        ->send();
                                                }),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(UserExporter::class)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('profile.birthdate')
                    ->label('Birthdate')
                    ->date(config('filament.date_format'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(config('filament.date_time_format'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
