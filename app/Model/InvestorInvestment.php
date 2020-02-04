<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InvestorInvestment extends Pivot
{
    protected $table = 'investor_investment_type';

    public $timestamps = false;

    protected $guarded = [];

}
