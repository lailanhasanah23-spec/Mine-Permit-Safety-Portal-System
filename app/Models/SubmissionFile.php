<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    protected $table = 'submission_files';

    protected $fillable = [
        'submission_id',
        'uploader_role',
        'file_type',
        'file_path',
        'file_name',
        'uploaded_by',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileTypeLabelAttribute(): string
    {
        return match ($this->file_type) {
            'ktp' => 'KTP',
            'mcu' => 'MCU',
            'sim' => 'SIM',
            'foto_diri' => 'Foto Diri',
            'hasil_verifikasi_tod' => 'Hasil Verifikasi Teknis (TOD)',
            default => strtoupper((string) $this->file_type),
        };
    }
}
