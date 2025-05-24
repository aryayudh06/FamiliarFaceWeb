<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Register2FA extends Model
{
    protected $fillable = [
        'name',
        'user_id', // Although user_id is set via relationship, it's good practice to include it if it might be mass assigned in other contexts.
    ];

    /**
     * Get the user that owns the 2FA registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // If you also want to allow guarding none, you could use
    // protected $guarded = [];
}
