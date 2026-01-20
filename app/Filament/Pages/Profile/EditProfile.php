<?php

namespace App\Filament\Pages\Profile;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                SchemaView::make('filament.pages.profile.mcp-tokens-wrapper'),
            ]);
    }
}
