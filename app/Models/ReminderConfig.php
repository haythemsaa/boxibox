<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderConfig extends Model
{
    use HasFactory;

    protected $table = 'reminders_config';

    protected $fillable = [
        'type',
        'days_before',
        'send_email',
        'send_sms',
        'email_template',
        'sms_template',
        'active',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'active' => 'boolean',
        'days_before' => 'integer',
    ];
}
