<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InvestorInvestorType extends Pivot
{
    protected $table = 'investor_investor_types';

    protected $guarded = [];

    public $timestamps = false;
}
