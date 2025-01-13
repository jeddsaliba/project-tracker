<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function form(Form $form): Form
    {
        return \App\Filament\Resources\TaskResource::form($form);
    }

    public function table(Table $table): Table
    {
        return \App\Filament\Resources\TaskResource::table($table);
    }
}
