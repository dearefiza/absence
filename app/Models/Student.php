<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = ['nisn', 'name', 'image', 'user_id', 'class_id'];

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'student_id');
    }

    public function attendanceForDate($date)
    {
        return StudentAttendance::query()->where('student_id', $this->id)->where('date', $date)->first();
    }

    public function attendancePerAcademic($id)
    {
        return StudentAttendance::query()->where('student_id', $this->id)->where('academic_year_id', $id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', "id");
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id', "id");
    }
}
