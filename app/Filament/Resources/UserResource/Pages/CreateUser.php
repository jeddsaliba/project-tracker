<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private $password = null;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->password = $data['password'];
        return $data;
    }
    
}
