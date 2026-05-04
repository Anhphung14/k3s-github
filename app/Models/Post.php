<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'description',
        'status',
        'author_id',
        'thumbnail',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    public function publish()
    {
        $this->status = 'published';
        $this->save();
    }
    public function unpublish()
    {
        $this->status = 'draft';
        $this->save();
    }
    public function excerpt($length = 100)
    {
        return substr($this->content, 0, $length) . (strlen($this->content) > $length ? '...' : '');
    }
    public function addComment($commentData)
    {
        return $this->comments()->create($commentData);
    }
    public function addTag($tag)
    {
        return $this->tags()->attach($tag);
    }
    public function removeTag($tag)
    {
        return $this->tags()->detach($tag);
    }
}

