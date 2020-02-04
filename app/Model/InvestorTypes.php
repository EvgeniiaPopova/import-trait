<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvestorTypes extends Model
{
    protected $table = 'investor_types';

    public $import_search = 'type';
}
