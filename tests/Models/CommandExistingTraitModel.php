<?php

namespace Shoboske\DataTableQueryBuilder\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Shoboske\DataTableQueryBuilder\Traits\DataTableQueryBuilderTrait;

class CommandExistingTraitModel extends Model
{
    use DataTableQueryBuilderTrait;

    protected $table = 'command_existing_trait_models';

    protected $fillable = [
        'title',
    ];
}
