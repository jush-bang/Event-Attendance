<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $event_id
 * @property int $session_number
 * @property int $day_number
 * @property string $session_date
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $status
 * @property int|null $user_id
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Support\Collection|\App\Models\Attendance[] $attendances
 */
class Session extends Model
{
    use HasFactory;

    protected $table = 'tbl_sessions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'event_id',
        'session_number',
        'day_number',
        'session_date',
        'start_time',
        'end_time',
        'status',
        'user_id',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the event that this session belongs to
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'e_id');
    }

    /**
     * Get the user who started this session
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all attendances for this session
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'session_id', 'id');
    }
}
