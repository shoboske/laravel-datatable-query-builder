<?php

namespace Shoboske\DataTableQueryBuilder\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class CommandFillableModel extends Model
{
    protected $table = 'command_fillable_models';

    protected $fillable = [
        'name',
        'email',
    ];
}
