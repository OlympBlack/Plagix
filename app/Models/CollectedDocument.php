<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'university',
        'publication_year',
        'published_at',
        'content',
        'description',
        'source_url',
        'hash',
        'scraping_source_id',
    ];

    protected $casts = [
        'published_at' => 'date',
    ];

    public function source()
    {
        return $this->belongsTo(ScrapingSource::class, 'scraping_source_id');
    }
}
