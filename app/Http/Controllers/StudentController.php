<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportStudentsRequest;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index()
    {

        $users = User::all();
        $classes = ClassModel::all();
        $students = Student::all();
        return view('student.index', compact('students', 'users', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|unique:students,nisn',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string',
            'user_id' => 'nullable',
            'class_id' => 'required',
        ]);

        $filename = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image'); // Retrieve the uploaded file from the request
            $filename = $file->getClientOriginalName();

            Storage::putFileAs('public/images', $file, $filename);
        }

        Student::create([
            'nisn' => $request->nisn,
            'image' => $filename,
            'name' => $request->name,
            'user_id' => $request->user_id,
            'class_id' => $request->class_id,
        ]);

        return redirect()->route('student.index')->with('success', 'Murid berhasil ditambahkan.');
    }

    public function show(Student $student)
    {
        return view('student.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $users = User::all();
        $classes = ClassModel::all();
        return view('student.edit', compact('student', 'users', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nisn' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'nullable|string',
            'user_id' => 'nullable',
            'class_id' => 'nullable',
        ]);

        $filename = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image'); // Retrieve the uploaded file from the request
            $filename = $file->getClientOriginalName();

            Storage::putFileAs('public/images', $file, $filename);
        }

        if (intval($request->status) === 1) Student::query()->update(['status' => 0]);

        $student->nisn = $request->nisn;
        try {
            DB::beginTransaction();
            if ($request->hasFile('image')) {
                if ($student->image) {
                    unlink(storage_path() . '/app/public/images/' . $student->image);
                }
                $student->image = $filename;
            }
            $student->name = $request->name;
            $student->user_id = $request->user_id;
            $student->class_id = $request->class_id;
            $student->save();

            DB::commit();

            return redirect()->route('student.index')->with('success', 'Murid berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return redirect()->route('student.index')->with('error', 'Terjadi kesalahan.');
        }
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('student.index')->with('success', 'Murid berhasil dihapus.');
    }

    public function pdf(Student $pdf)
    {
        $pdf = new \Mpdf\Mpdf();
        $pdf->WriteHTML('<h1>Hello Word!!</h1>');
        $pdf->Output();
    }


    public function import(ImportStudentsRequest $request)
    {
        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file));

        // Check column count
        $header = $data[0];
        if (count($header) !== 2) {
            return back()->withErrors(['The CSV must contain exactly 2 columns: nisn, name.']);
        }

        // Remove header
        array_shift($data);

        // Validate each row
        foreach ($data as $row) {
            $validator = Validator::make([
                'nisn' => $row[0],
                'name' => $row[1],
            ], [
                'nisn' => 'required|string|max:255',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }

        // Insert data
        foreach ($data as $row) {
            Student::create([
                'image' => null,
                'nisn' => $row[0],
                'name' => $row[1],
            ]);
        }

        return back()->with('success', 'Berhasil inport murid.');
    }
}
