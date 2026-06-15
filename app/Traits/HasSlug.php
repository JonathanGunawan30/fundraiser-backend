<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HasSlug
{
    /**
     * Boot the trait and register the creating/updating events.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            $model->generateSlug();
        });

        static::updating(function (Model $model) {
            if ($model->isDirty($model->getSlugSourceField())) {
                $model->generateSlug();
            }
        });
    }

    /**
     * Generate a unique slug for the model.
     */
    public function generateSlug(): void
    {
        $source = $this->getAttribute($this->getSlugSourceField());
        
        if (empty($source)) {
            return;
        }

        $baseSlug = Str::slug($source);
        
        // Clean up double dashes or spaces (Str::slug handles most, but let's be sure)
        $baseSlug = preg_replace('/-+/', '-', $baseSlug);
        $baseSlug = trim($baseSlug, '-');

        $slug = $baseSlug;
        $counter = 1;

        // Ensure uniqueness
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $this->setAttribute('slug', $slug);
    }

    /**
     * Check if a slug already exists in the table.
     */
    protected function slugExists(string $slug): bool
    {
        return static::where('slug', $slug)
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->exists();
    }

    /**
     * Get the field to generate the slug from.
     * Override this in the model if needed.
     */
    protected function getSlugSourceField(): string
    {
        return property_exists($this, 'slugSourceField') ? $this->slugSourceField : 'name';
    }
}
