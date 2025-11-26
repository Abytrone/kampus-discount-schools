<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends Model
{
    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'phone',
        'address',
        'email',
    ];

    /**
     * Get the student that owns the guardian.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

