<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCheck extends Model
{
    protected $fillable = [
        'mortgage_application_id',
        'credit_score',
        'credit_report_data',
        'status',
        'checked_at',
    ];

    protected $casts = [
        'credit_report_data' => 'array',
        'checked_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MortgageApplication::class, 'mortgage_application_id');
    }
}

class ApplicationDocument extends Model
{
    protected $fillable = [
        'mortgage_application_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MortgageApplication::class, 'mortgage_application_id');
    }
}

class ApplicationEvent extends Model
{
    protected $fillable = [
        'mortgage_application_id',
        'event_type',
        'old_value',
        'new_value',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MortgageApplication::class, 'mortgage_application_id');
    }
}
