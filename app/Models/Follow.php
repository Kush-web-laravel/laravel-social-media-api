<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follow extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = ['follower_id', 'following_id', 'is_accepted'];

    protected $dates = ['deleted_at'];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
