<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'emplacement_id',
        'background_image',
        'canvas_width',
        'canvas_height',
    ];

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }
}
