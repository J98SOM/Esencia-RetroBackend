<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'token', 'name', 'last_used_at'])]
class ApiToken extends Model
{
    /**
     * Get the user that owns the API token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
