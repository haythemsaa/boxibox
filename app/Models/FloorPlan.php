<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloorPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'emplacement_id',
        'nom',
        'path_data',
        'canvas_width',
        'canvas_height',
        'echelle_metres_par_pixel',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'echelle_metres_par_pixel' => 'decimal:4',
    ];

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }

    public function boxes()
    {
        return $this->emplacement->boxes();
    }
}
