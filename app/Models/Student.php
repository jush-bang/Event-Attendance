<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $snumber
 * @property string $name
 * @property string|null $rfid
 * @property string $section
 * @property string|null $program
 */
class Student extends Model
{
    use HasFactory;

    protected $table = 'tbl_students';
    protected $primaryKey = 'snumber';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'snumber',
        'e_id',
        'name',
        'section',
        'rfid',
        'program',
    ];

    /**
     * Get attendance records for this student
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'snumber', 'snumber');
    }
}
