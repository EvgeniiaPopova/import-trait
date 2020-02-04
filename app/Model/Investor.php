<?php

namespace App;

use App\Traits\Importable;
use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    use Importable;

    const ONE_MILLION = 1000000;
    const ONE_THOUSAND = 1000;

    protected $table = 'investors';

    public $fields_to_import = [
        "common_name",
        "company_name",
        "individual_name_contact",
        "city",
        "investment_stage",
        "email",
        "phone",
        "address",
        "website",
        "region_for_investing"
    ];

    public $import_search = 'id';

    public $ranges = [
        'investment_range' => ['field' => 'inv_*_range', 'separator' => '|', 'format' => ['k' => self::ONE_THOUSAND, 'M' => self::ONE_MILLION, 'M+' => self::ONE_MILLION]],
        'debt_investment_term' => ['field' => 'inv_*_term', 'separator' => '|', 'format' => ['mo' => 1, 'yr' => 12, '+yr' => 12]],
    ];

    public $pivots = [
        'investor_type' => ['model' => InvestorInvestorType::class, 'pivot_model' => ['class' => InvestorTypes::class, 'field' => 'inv_type_id']],
        'investment_type' => ['model' => InvestorInvestment::class, 'pivot_model' => ['class' => InvestmentTypes::class, 'field' => 'inv_type_id']],
        'investment_industry' => ['model' => InvestorIndustries::class, 'pivot_model' => ['class' => Industry::class, 'field' => 'industry_id']],
    ];

    public $foreigns = [
        'currency' => Currency::class,
        'country' => Country::class
    ];

    protected $guarded = [];
}
