<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'internal_companies';

    protected $fillable = [
        'company_name',
        'group_id',
        'code',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    /**
     * Get all vendors for dropdown/autocomplete
     */
    public static function forDropdown()
    {
        return static::where('group_id', '!=', 58)
            ->orderBy('company_name')
            ->pluck('company_name', 'id');
    }

    /**
     * Find vendor by company name
     */
    public static function byName($name)
    {
        return static::where('company_name', $name)
            ->where('group_id', '!=', 58)
            ->first();
    }
}
