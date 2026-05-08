<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Attendance Model
 * 
 * @property-read int $id
 * @property int $event_id
 * @property int|null $session_id
 * @property string $snumber
 * @property string|null $time_in
 * @property string|null $time_out
 * @property array|null $cycles_data
 * @property string|null $status
 * @property int|null $total_duration_minutes
 * @property \DateTime|null $last_scan_time
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Session $session
 */
class Attendance extends Model
{
    protected $table = 'tbl_attendance';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'event_id',
        'session_id',
        'snumber',
        'time_in',
        'time_out',
        'cycles_data',
        'status',
        'total_duration_minutes',
        'last_scan_time',
    ];

    protected $casts = [
        'cycles_data' => 'json',
        'status' => 'string',
        'total_duration_minutes' => 'integer',
        'last_scan_time' => 'datetime',
    ];

    /**
     * Get the event for this attendance record
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'e_id');
    }

    /**
     * Get the student for this attendance record
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'snumber', 'snumber');
    }

    /**
     * Get the session for this attendance record
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
}
