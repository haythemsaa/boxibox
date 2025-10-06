<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'titre',
        'message',
        'lien',
        'lu'
    ];

    protected $casts = [
        'lu' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the client that owns the notification.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
