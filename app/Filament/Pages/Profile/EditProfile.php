<?php

namespace App\Filament\Pages\Profile;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected function getForms(): array
    {
        return [
            'editProfileForm' => $this->makeForm()
                ->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ])
                ->statePath('data'),
        ];
    }

    public function getExtraComponents(): array
    {
        return [
            \Illuminate\Support\Facades\Blade::render('@livewire(\'profile.manage-mcp-tokens\')'),
        ];
    }
}
