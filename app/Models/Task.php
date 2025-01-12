<?php

namespace App\Models;

use App\Enums\MediaCollectionType;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([TaskObserver::class])]
class Task extends Model implements HasMedia
{
    use HasFactory, HasFilamentComments, InteractsWithMedia, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'expected_completed_date',
        'actual_completed_date'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'expected_completed_date' => 'date',
            'actual_completed_date' => 'date'
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::TASK->value)
            ->registerMediaConversions($this->modelMediaConvertionRegistrations());
    }
    /** callable for registerMediaConversions */
    public function modelMediaConvertionRegistrations(): callable
    {
        return function () {
            $this->addMediaConversion('original')->nonOptimized()->nonQueued();
            $this->addMediaConversion('small')->fit(Fit::Crop, 300, 300)->nonQueued();
            $this->addMediaConversion('medium')->fit(Fit::Crop, 500, 500)->nonQueued();
            $this->addMediaConversion('large')->fit(Fit::Crop, 800, 800)->quality(80)->nonQueued();
        };
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function status(): BelongsToMany
    {
        return $this->belongsToMany(Status::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    protected function checklistCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->checklist()->where(['is_done' => true])->count() . " / " . $this->checklist->count()
        );
    }
}
