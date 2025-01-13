<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function form(Form $form): Form
    {
        return \App\Filament\Resources\ProjectResource::form($form);
    }

    public function table(Table $table): Table
    {
        return \App\Filament\Resources\ProjectResource::table($table);
    }
}
