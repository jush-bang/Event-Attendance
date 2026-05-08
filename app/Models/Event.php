<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $e_id
 * @property string $e_name
 * @property string $start_date
 * @property string $end_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int $sessions
 * @property string $e_location
 * @property string $e_status
 * @property bool $require_action_prompts
 * @property string|null $status
 * @property string|null $archived_at
 * @property string|null $archived_delete_at
 * @property \Illuminate\Support\Collection|\App\Models\Attendance[] $attendances
 * @property \Illuminate\Support\Collection|\App\Models\Session[] $sessions
 * @property-read \App\Models\User|null $user
 */
class Event extends Model
{
    use HasFactory;

    protected $table = 'tbl_event';
    protected $primaryKey = 'e_id';
    public $timestamps = true;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'e_name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'sessions',
        'e_location',
        'e_status',
        'require_action_prompts',
        'status',
        'archived_at',
        'archived_delete_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
        'archived_delete_at' => 'datetime',
    ];

    /**
     * Get the attendance records for this event
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'event_id', 'e_id');
    }

    /**
     * Get the sessions for this event
     */
    public function sessions()
    {
        return $this->hasMany(Session::class, 'event_id', 'e_id');
    }

    /**
     * Get the user responsible for this event
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Event status alias for e_status column.
     */
    public function getStatusAttribute()
    {
        return $this->attributes['e_status'] ?? null;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['e_status'] = $value;
    }

    /**
     * Archived at attribute accessor.
     */
    public function getArchivedAtAttribute($value)
    {
        return $value;
    }

    /**
     * Archived delete at attribute accessor.
     */
    public function getArchivedDeleteAtAttribute($value)
    {
        return $value;
    }
}
