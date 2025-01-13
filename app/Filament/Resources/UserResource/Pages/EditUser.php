<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (empty($data['password_confirmation'])) {
            unset($data['password_confirmation']);
        }

        $profile = $data['profile'] ?? [];
        unset($data['profile']);

        $this->record->profile()->updateOrCreate([], $profile);

        return $data;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
