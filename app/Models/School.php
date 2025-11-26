<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'school_code',
        'name',
        'category',
        'school_type',
        'gender_type',
        'region',
        'district',
        'location',
        'address',
        'programmes',
        'num_programmes',
        'status_type',
        'status',
        'contact_phone',
        'contact_email',
        'serial_number',
    ];

    protected $casts = [
        'school_code' => 'integer',
        'num_programmes' => 'integer',
        'serial_number' => 'integer',
        'programmes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the students for this school.
     */
    public function students(): HasMany
    {
        return $this->hasMany(\App\Models\Student::class);
    }

    /**
     * Get the users for this school.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the active students count.
     */
    public function getActiveStudentsCountAttribute(): int
    {
        return $this->students()->where('admission_status', 'Active')->count();
    }

    /**
     * Get the total students count.
     */
    public function getTotalStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }


    /**
     * Scope a query to only include active schools.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope a query to filter by region.
     */
    public function scopeInRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope a query to filter by school type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('school_type', $type);
    }

    /**
     * Scope a query to filter by gender type.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender_type', $gender);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
