<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'linkedin_url',
        'employees',
    ];

    /**
     * Get the persons that belong to the company.
     */
    public function persons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Person::class);
    }
}
