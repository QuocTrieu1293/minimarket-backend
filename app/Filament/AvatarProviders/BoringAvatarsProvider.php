<?php 

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

class BoringAvatarsProvider implements AvatarProvider {
    public function get(Model $record): string {
        return 'https://source.boringavatars.com/beam/160/' . urlencode($record->email);
    }
}


?>