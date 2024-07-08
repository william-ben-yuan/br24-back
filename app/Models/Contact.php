<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'last_name',
        'email',
        'phone',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
