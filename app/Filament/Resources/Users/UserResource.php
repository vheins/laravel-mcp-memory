<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\RelationManagers\EntityTermsRelationManager;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static UnitEnum|string|null $navigationGroup = 'Access Management';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EntityTermsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }
}
