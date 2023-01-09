<?php

namespace Nox\Framework\Module\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class PackagistModule extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'name';

    public function getSchema(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'url' => 'string',
            'downloads' => 'integer',
        ];
    }

    public function getRows(): array
    {
        return [];
    }
}
