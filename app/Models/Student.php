<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'kd_shs_id',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'photo_url',
        'school_id',
        'form',
        'programme',
        'house',
        'index_number',
        'ghana_card_number',
        'year_of_admission',
        'completion_year',
        'admission_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the school that owns the student.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the guardians for this student.
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    /**
     * Get the verifications for this student.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }

    /**
     * Get the latest verification.
     */
    public function latestVerification()
    {
        return $this->hasOne(Verification::class)->latestOfMany();
    }
}

