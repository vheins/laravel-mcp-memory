<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Permission extends SpatiePermission
{
    use HasFactory;
    use Cachable;
    //
}
