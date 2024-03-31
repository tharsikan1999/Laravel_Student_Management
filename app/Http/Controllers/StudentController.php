<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Students;

use function Pest\Laravel\get;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Fetch paginated students data
    $students = Students::orderBy('id', 'desc')->paginate(7); // Assuming 10 students per page

    // Pass the paginated data to the Vue component
    return inertia('Students', [
        'students' => $students->items(),
        'currentPage' => $students->currentPage(),
        'lastPage' => $students->lastPage(),
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Student/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'email' => 'required|email',
                'firstname' => 'required',
                'lastname' => 'required',
                'age' => 'required|numeric|min:1|max:100',
                'status' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            // Check if an image file is provided
            if ($request->hasFile('image')) {
                // Get the original file name
                $originalName = $request->file('image')->getClientOriginalName();
    
                // Generate a unique file name
                $fileName = time() . '_' . $originalName;
    
                // Move the file to the 'public/images' directory
                $request->file('image')->move(public_path('images'), $fileName);
            } else {
                // If no image is provided, set $fileName to null
                $fileName = null;
            }
    
            // Create a new student record
            $student = Students::create([
                'email' => $request->email,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'age' => $request->age,
                'status' => $request->status,
                'image' => $fileName ? 'images/' . $fileName : null, // Assign the path to the 'image' column
            ]);
    
            // Redirect with success message if student is created successfully
            return redirect()->route('students.index')->with('success', 'Student created successfully.');
    
        } catch (\Exception $e) {
            // Handle any errors
            return redirect()->back()->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }
    


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Students $Student)
    {
        $student = Students::find($Student->id);
        
        return inertia('Student/Edit', ['student' => $student]);
        
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Students $Student)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email ',
            'firstname' => 'required', // Corrected field name
            'lastname' => 'required',
            'age' => 'required|numeric | min:1  | max:100',
            'status' => 'required',
        ]);
        

        $data = $request->all();

        if($Student->update($data)){
            
            return redirect()->route('students.index')->with('success', 'Student updated successfully');
        }
        else{
            dd('Student not updated');
        }
        
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Students $Students)
    {
        // Delete the file associated with the student if it exists
        if ($Students->image) {
            $filePath = public_path($Students->image);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    
        // Delete the student record
        $Students->delete();
    
        return redirect()->route('students.index')->with('success', 'Student deleted successfully');
    }
    
}
