<?php

namespace App\Models;

use App\Models\Concerns\HasClientUuids;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    use HasClientUuids;
}
