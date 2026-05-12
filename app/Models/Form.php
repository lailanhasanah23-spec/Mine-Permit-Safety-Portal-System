<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends Model
{
    protected $table = 'forms';

    protected $fillable = [
        'category_id',
        'title',
        'purpose',
        'form_url',
        'link_scope',
        'notes',
        'effective_start',
        'effective_end',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
