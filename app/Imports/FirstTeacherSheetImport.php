<?php

namespace App\Imports;

use App\User;
use App\Myclass;
use App\Section;
use App\Department;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
//use Maatwebsite\Excel\Concerns\WithChunkReading;

class FirstTeacherSheetImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    protected $class, $section, $department;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->class = (string) $row['class'];
        $this->section = (string) $row['section'];
        $this->department = $row['department'];

        return new User([
                'name'           => $row['name'],
                'email'          => $row['email'],
                'password'       => Hash::make($row['password']),
                'active'         => 1,
                'role'           => 'teacher',
                'school_id'      => auth()->user()->school_id,
                'code'           => auth()->user()->code,
                'student_code'   => auth()->user()->school_id.date('y').substr(number_format(time() * mt_rand(), 0, '', ''), 0, 5),
                'address'        => $row['address'],
                'about'          => $row['about'],
                'pic_path'       => '',
                'phone_number'   => $row['phone_number'],
                'verified'       => 1,
                'section_id'     => $this->getSectionId(),// For assigning as class teacher
                'blood_group'    => $row['blood_group'] ?? '',
                'nationality'    => $row['nationality'] ?? '',
                'gender'         => $row['gender'],
                'department_id'  => $this->getDepartmentId(),
        ]);
    }

    public function getSectionId(){
        if(!empty($this->class) && !empty($this->section)){
            $class_id = Myclass::bySchool(auth()->user()->school_id)->where('class_number', $this->class)->pluck('id')->first();

            return Section::where('class_id', $class_id)->where('section_number', $this->section)->pluck('id')->first();
        } else {
            return 0;
        }
    }

    public function getDepartmentId(){
        return Department::bySchool(auth()->user()->school_id)->where('department_name',$this->department)->pluck('id')->first();
    }

    public function batchSize(): int
    {
        return 200;
    }
}
