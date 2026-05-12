<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $table = 'submissions';

    protected $fillable = [
        'category_id',
        'applicant_name',
        'item_type',
        'item_identifier',
        'item_details',
        'status',
        'gdrive_folder_id',
        'she_notes',
        'paramedic_notes',
        'rejected_at',
        'approved_at',
        'paramedic_verified_at',
        'created_by',
        'vendor_id',
        'submitted_by_vendor',
    ];

    protected $casts = [
        'item_details' => 'array',
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'paramedic_verified_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'submission_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_hrga' => 'Perlu Dilengkapi',
            'pending_paramedic' => 'Menunggu Verifikasi Medis',
            'pending_tod' => 'Menunggu Verifikasi Teknis',
            'pending_she' => 'Menunggu Persetujuan SHE',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she' => 'is-warning',
            'approved' => 'is-success',
            'rejected' => 'is-danger',
            default => 'is-neutral',
        };
    }
}
