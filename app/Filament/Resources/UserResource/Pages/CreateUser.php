<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Notifications\NewUserNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
