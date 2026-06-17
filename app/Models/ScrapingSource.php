<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'is_active',
        'last_run_at',
        'documents_collected',
        'scraping_status',
        'scraping_progress',
        'current_page',
        'total_pages',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function documents()
    {
        return $this->hasMany(CollectedDocument::class);
    }
}
