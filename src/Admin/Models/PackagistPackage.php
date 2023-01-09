<?php

namespace Nox\Framework\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class PackagistPackage extends Model
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
