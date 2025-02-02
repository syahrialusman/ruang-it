<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class)->orderBy('sequence', 'asc');
    }
}
