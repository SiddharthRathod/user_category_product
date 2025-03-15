<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'parent_id'];

    /**
     * Get the parent category of this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the immediate child categories of this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all active child categories recursively (N-level hierarchy).
     * This function loads children categories along with their children.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive')->where('status','active');
    }

}

