<?php

namespace Nox\Framework\Admin\Contracts;

use Illuminate\Database\Eloquent\Model;

interface IsActivitySubject
{
    public function getActivitySubjectDescription(Model $record): string;
}
