<?php

namespace App\Http\Controllers;

use App\Models\address;
use App\Models\profile;
use App\Models\assessment;
use App\Models\assign;
use App\Models\attendance;
use App\Models\classes;
use App\Models\corevalues;
use App\Models\grade;
use App\Models\payment_form;
use App\Models\previous_school;
use App\Models\QuarterSettings;
use App\Models\register_form;
use App\Models\required_docs;
use App\Models\section;
use App\Models\studentdetails;
use App\Models\teacher;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DB;

class Pagecontroller extends Controller
{

    public function user()
    {
        $users = User::all();
        return view('user', compact('users'));
    }
    public function login()
    {
        return view('login');
    }
    public function partialaccount()
    {
        //sweetalert()->warning('This is your partial account so please fill up all the details asked.');
        return view('partialaccount');
    }
    public function register_consent()
    {
       // sweetalert()->info('This is optional only but we need the consent of your parents first.');
        return view('register_consent');
    }

    public function studentdetails()
    {
        $details = studentdetails::all();

        return view('studentdetails', compact('details'));
    }

    public function address_contact()
    {
        return view('address_contact'); 
    }

    public function required_documents()
    {
        return view('required_documents');
    }

    public function previous_school()
    {
        $userId = Auth::user()->id; 
        return view('previous_school',compact('userId'));
    }

    public function payment_process()
    {
        return view('payment_process');
    }

    public function studentdashboard()
    {
        $userId = Auth::id();
    
      
        $profile = register_form::where('user_id', $userId)->firstOrFail();
        

        $picture = profile::where('user_id', $userId)->first();
    
       
      
       
    
        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
           
            'picture' => $picture 
        ];
    
        return view('studentdashboard', $data);
    }
    public function studentclassload()
{
    $userId = Auth::id(); 

    $picture = Profile::where('user_id', $userId)->first(); 
    $registerForm = \App\Models\register_form::where('user_id', $userId)->first();

    if (!$registerForm) {
        return redirect()->route('login')->withErrors('No registration form found.');
    }

    $assignedClasses = assign::where('class_id', $registerForm->id)->get();

    $student = $registerForm;

    $proof = payment_form::where('payment_id', $registerForm->id)->first();

    return view('studentclassload', [
        'assignedClasses' => $assignedClasses,
        'student' => $student,
        'proof' => $proof,
        'picture' => $picture
    ]);
}


public function oldstudentclassload()
{
    $userId = Auth::id(); // Get the authenticated user's ID

    // Find the register form associated with the authenticated user
    $registerForm = \App\Models\register_form::where('user_id', $userId)->first();

    if (!$registerForm) {
        return redirect()->route('login')->withErrors('No registration form found.');
    }

    $picture = Profile::where('user_id', $userId)->first();

    $assignedClasses = assign::where('class_id', $registerForm->id)->get();

    $student = $registerForm;

    $proof = payment_form::where('payment_id', $registerForm->id)->first();

    return view('oldstudentclassload', [
        'assignedClasses' => $assignedClasses,
        'student' => $student,
        'proof' => $proof,
        'picture' => $picture
    ]);
}

    public function studentprofile()
    {
        $userId = Auth::id();
    

        $profile = register_form::where('user_id', $userId)->firstOrFail();
        
   
        $picture = Profile::where('user_id', $userId)->first(); // Get the specific user's picture
        $level = payment_form::where('payment_id', $profile->id)->first();
  
    
        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'level' => $level,
            'picture' => $picture
        ];
    
        return view('studentprofile', $data);
    }

    public function studenteditprofile()
    {
        $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('studenteditprofile', $data);
    }

    public function oldstudentprofile()
    {
        $userId = Auth::id();
    

        $profile = register_form::where('user_id', $userId)->firstOrFail();
        
   
        $picture = Profile::where('user_id', $userId)->first(); // Get the specific user's picture
        $level = payment_form::where('payment_id', $profile->id)->first();
    
        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'level' => $level,
            'picture' => $picture
        ];
    
        return view('oldstudentprofile', $data);
    }

    public function editprofile()
    {
        
        $userId = Auth::id();
    

        $profile = register_form::where('user_id', $userId)->firstOrFail();
        
   
        $picture = Profile::where('user_id', $userId)->first(); // Get the specific user's picture
        $level = payment_form::where('payment_id', $profile->id)->first();
    
        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'level' => $level,
            'picture' => $picture
        ];
    
        return view('editprofile', $data);
    }

    public function enrollmentStep()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        //Log::info("Authenticated User ID: " . $userId); // Log the user ID
    } else {
        //Log::info("No user is authenticated.");
    }

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $registerForm = register_form::where('user_id', $user->id)->first(); 

    if (!$registerForm) {
        return redirect()->route('some.route')->with('error', 'No registration form found.');
    }

    $details = studentdetails::where('details_id', $registerForm->id)->first();
    $address = address::where('address_id', $registerForm->id)->first(); 
    $previousSchool = previous_school::where('school_id', $registerForm->id)->first();
    $requiredDocs = required_docs::where('required_id', $registerForm->id)->first();
    $payment = payment_form::where('payment_id', $registerForm->id)->first(); 
    $assignStatus = assign::where('class_id', $registerForm->id)->first(); 
    $picture = Profile::where('user_id', $userId)->first();
    return view('enrollmentstep', compact('details', 'address', 'previousSchool', 'requiredDocs', 'payment', 'assignStatus',  'picture'));
}

public function studentgrades()
{
    $userId = Auth::id();
    $user = Auth::user();
    $userName = trim("{$user->firstname} {$user->middlename} {$user->lastname}");

    $grades = Grade::where('fullname', $userName)
                   ->where('status', 'approved')
                   ->get(['subject', 'edp_code', 'section', '1st_quarter', '2nd_quarter', '3rd_quarter', '4th_quarter', 'overall_grade', 'grade_id']);

    $gradesApproved = $grades->isNotEmpty();
    $gradeId = $grades->first()->grade_id ?? null;
    $picture = Profile::where('user_id', $userId)->first();

    $coreId = null;
    if ($gradeId) {
        $coreId = corevalues::where('section', $grades->first()->section)->value('core_id'); 
    }

    $attendanceId = null;
    if ($userName) {
        $attendanceId = Attendance::where('fullname', $userName)->value('attendance_id'); 
    }

    return view('studentgrades', [
        'grades' => $grades,
        'gradesApproved' => $gradesApproved,
        'studentId' => $userId,
        'gradeId' => $gradeId,
        'coreId' => $coreId,
        'attendanceId' => $attendanceId,
        'picture' => $picture
    ]);
}

public function studentassessment(Request $request)
{
    $schoolYears = assessment::select('school_year')->distinct()->pluck('school_year');

    $user = Auth::user();
    if (Auth::check()) {
        $userId = Auth::user()->id;
        //Log::info("Authenticated User ID: " . $userId); // Log the user ID
    } else {
        //Log::info("No user is authenticated.");
    }
    $picture = Profile::where('user_id', $userId)->first();
    $paymentId = $user ? $user->payment_id : null; // Safely get payment_id

    $userPayment = payment_form::where('payment_id', $paymentId)->first();
    $authGradeLevel = $userPayment ? strtolower(str_replace(' ', '', trim($userPayment->level))) : null;

    // Start with published assessments
    $assessments = assessment::where('status', 'Published');

    // Filter assessments by school year if selected
    if ($request->has('school_year') && $request->school_year !== '') {
        $assessments = $assessments->where('school_year', $request->school_year);
    }

    // Filter by the authenticated user's grade level (exact match)
    if ($authGradeLevel) {
        $assessments = $assessments->where('grade_level', '=', $authGradeLevel); // Exact match
    }

    // Get all assessments
    $assessments = $assessments->get();

    // Return the view with assessments, school years, and the user's grade level
    return view('studentassessment', compact('assessments', 'schoolYears', 'authGradeLevel', 'picture'));
}

    //teacher 
    public function teacher()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('teacher', compact('picture'));
    }
    public function teachernotification()
    {
        return view('teachernotification');
    }
    public function teacherprofile()
    {
        $user = Auth::user();
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('teacherprofile', ['user' => $user, 'picture' => $picture]);
    }

    public function teachereditprofile()
    {
        $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('teachereditprofile', $data);
    }

    public function teacherclassload()
    {
       
        $authUserId = Auth::id();
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        // Fetch classes where teacher_id matches the authenticated user's ID
        $classes = Classes::where('teacher_id', $authUserId)
            ->select('section', 'edpcode', 'subject', 'grade', 'teacher_id')
            ->get();
    
        // Log the assigned classes for the authenticated teacher
        Log::info('Assigned Classes for Authenticated Teacher: ', $classes->toArray());
    
        if ($classes->isEmpty()) {
            Log::warning('No classes found for the authenticated teacher ID.');
        }
    
        // Fetch proofs where level is not null
        $proofs = payment_form::whereNotNull('level')->get(); 
    
        // Return the view with the classes and proofs
        return view('teacherclassload', [
            'title' => 'Teacher Class Load',
            'classes' => $classes,
            'proofs' => $proofs ,
            'picture' => $picture
        ]);
    }


    
    public function teacherattendance()
    {
        // Get the authenticated user's ID
        $authUserId = Auth::id();
       
        $picture = Profile::where('user_id', $authUserId)->first(); 
        // Fetch classes where teacher_id matches the authenticated user's ID
        $classes = Classes::where('teacher_id', $authUserId)
            ->select('section', 'edpcode', 'subject', 'grade', 'teacher_id')
            ->get();
    
        // Log the assigned classes for the authenticated teacher
        Log::info('Assigned Classes for Authenticated Teacher: ', $classes->toArray());
    
        if ($classes->isEmpty()) {
            Log::warning('No classes found for the authenticated teacher ID.');
        }
    
        // Fetch proofs where level is not null
        $proofs = payment_form::whereNotNull('level')->get(); 
    
        // Return the view with the classes and proofs
        return view('teacherattendance', [
            'title' => 'Teacher Class Load',
            'classes' => $classes,
            'proofs' => $proofs,
            'picture' => $picture
        ]);
    }

    public function teachercorevalue()
    {
        // Get the authenticated user's ID
        $authUserId = Auth::id();
        $picture = Profile::where('user_id', $authUserId)->first(); 
        // Fetch classes where teacher_id matches the authenticated user's ID
        $classes = Classes::where('teacher_id', $authUserId)
            ->select('section', 'edpcode', 'subject', 'grade', 'teacher_id')
            ->get();
    
        // Log the assigned classes for the authenticated teacher
        Log::info('Assigned Classes for Authenticated Teacher: ', $classes->toArray());
    
        if ($classes->isEmpty()) {
            Log::warning('No classes found for the authenticated teacher ID.');
        }
    
        // Fetch proofs where level is not null
        $proofs = payment_form::whereNotNull('level')->get(); 
    
        // Return the view with the classes and proofs
        return view('teachercorevalue', [
            'title' => 'Teacher Class Load',
            'classes' => $classes,
            'proofs' => $proofs,
            'picture' => $picture
        ]);
    }


    //principal
    public function principal()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('principal', compact( 'picture'));
    }

    public function principalclassload()
    {
        
        $section = section::all();
        $class = classes::all();
        $teachers = teacher::all();
        return view('principalclassload', compact('class', 'teachers', 'section', 'picture'));
    }
    
    public function showEvaluateGrades(Request $request)
    {
        $quarterSettings = QuarterSettings::first();
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        if (!$quarterSettings) {
            $quarterSettings = new QuarterSettings();
            $quarterSettings->first_quarter_enabled = false;
            $quarterSettings->second_quarter_enabled = false;
            $quarterSettings->third_quarter_enabled = false;
            $quarterSettings->fourth_quarter_enabled = false;
            $quarterSettings->quarter_status = 'inactive'; 
        }
    
        $quartersEnabled = [
            '1st_quarter' => $quarterSettings->first_quarter_enabled,
            '2nd_quarter' => $quarterSettings->second_quarter_enabled,
            '3rd_quarter' => $quarterSettings->third_quarter_enabled,
            '4th_quarter' => $quarterSettings->fourth_quarter_enabled,
        ];
    
        $quartersStatus = [
            '1st_quarter' => $quarterSettings->quarter_status,
            '2nd_quarter' => $quarterSettings->quarter_status,
            '3rd_quarter' => $quarterSettings->quarter_status,
            '4th_quarter' => $quarterSettings->quarter_status,
        ];
    
        $assignsQuery = classes::distinct()->select('teacher_id', 'subject', 'adviser', 'section','grade');

        $assigns = $assignsQuery->get();
        $userId = Auth::id();
        
        $picture = Profile::where('user_id', $userId)->first(); 
        
        $grades = grade::where('status', 'pending')->get()->unique('subject');
    
        return view('submittedgrades', [
            'quartersEnabled' => $quartersEnabled,
            'quartersStatus' => $quartersStatus,
            'quarterSettings' => $quarterSettings,
            'assigns' => $assigns,
            'grades' => $grades,
            'picture' => $picture
        ]);
    }

    public function publishgrade()
    {
        $userId = Auth::id();
    

        $picture = profile::where('user_id', $userId)->firstOrFail();
        
        return view('publishgrade', compact('picture'));
    }

    //accounting
    public function accounting()
    {
        $userId = Auth::id();
    
      
        $picture = profile::where('user_id', $userId)->first(); 
        return view('accounting', compact('picture'));
    }

    public function accountingassessment()
    {
        $userId = Auth::id();
        $assessments = assessment::all(); 
        $picture = profile::where('user_id', $userId)->first();
        return view('accountingassessment', compact('assessments', 'picture'));
    }

    public function createassessment()
    {   
        $user = Auth::user();
        $userId = Auth::id();
        $picture = profile::where('user_id', $userId)->first();
        return view('createassessment', ['user' => $user, 'picture' => $picture ]);
    }
    public function accountingprofile()
    {
        $user = Auth::user();
        $userId = Auth::id();
        $picture = profile::where('user_id', $userId)->first(); 
        return view('accountingprofile', ['user' => $user, 'picture' => $picture]);
    }

    public function accountingeditprofile()
    {
        $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('accountingeditprofile', $data);
    }

    //record
    public function record()
    {
        $userId = Auth::id();
    
        $pendingCount = register_form::where('status', 'pending')->count();
        $approvedCount = register_form::where('status', 'approved')->count();

        $picture = Profile::where('user_id', $userId)->first(); 
        $students = studentdetails::all(); 

        return view('record', compact('students', 'pendingCount', 'approvedCount', 'picture'));
    }

    public function recordprofile()
    {
        $user = Auth::user();
        if (Auth::check()) {
            $userId = Auth::user()->id;
            Log::info("Authenticated User ID: " . $userId); // Log the user ID
        } else {
            Log::info("No user is authenticated.");
        }
        
        $picture = Profile::where('user_id', $userId)->first();

        return view('recordprofile', ['user' => $user,
    'picture' => $picture]);
    }

    public function recordeditprofile()
    {
        $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('recordeditprofile', $data);
    }

    public function studententries()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        
        $studentDetails = studentdetails::with('register')->get(); // Eager load register
    
        // Your other datasets
        $previousSchools = previous_school::all();
        $addresses = address::all();
        $requiredDocs = required_docs::all();
    
        // Pass all datasets to the view
        return view('studententries', compact('studentDetails', 'previousSchools','picture', 'addresses', 'requiredDocs'));
    }

    public function showdetails()
    {
        return view('showdetails');
    }

    public function studentapplicant()
    {
       $userId = Auth::id();
        $account = register_form::where('status', register_form::STATUS_PENDING)->get();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('studentapplicant', compact('account', 'picture'));
    }

    public function approvedaccount()
    {
        $userId = Auth::id();
        $account = register_form::where('status', 'approved')->get();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('approvedaccount', compact('account', 'picture'));
    }
    public function  recordapproval()
    {
        $account = register_form::all();
        return view(' recordapproval', compact('account'));
    }

    //cashier 
    public function cashier()
    {
        $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first();
        $pendingCount = payment_form::where('status', 'pending')->count();
        $approvedCount = payment_form::where('status', 'approved')->count();
        $students = studentdetails::all();
        return view('cashier', compact('students', 'pendingCount', 'picture','approvedCount'));
    }
    public function cashieraddfee()
    {
        return view('cashieraddfee');
    }
    public function cashierstudentfee()
{
    $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first();
    $students = register_form::all();
    
    // Fetch all payments
    $payments = payment_form::all();

    // Pass both students and payments to the view
    return view('cashierstudentfee', [
        'students' => $students,
        'payments' => $payments,
        'picture' => $picture
    ]);
}

public function cashierprofile()
{
    $user = Auth::user();   
    $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first();
    return view('cashierprofile', ['user' => $user, 'picture' => $picture]);
}

public function cashiereditprofile()
{
    $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('cashiereditprofile', $data);
}

public function principalassessment()
{
    $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first();
    $assessments = Assessment::all(); 
    return view('principalassessment', compact('assessments', 'picture'));
}

public function principaleditassessment()
{
    return view('principaleditassessment');
}

public function principalprofile()
{
    $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first(); 
      
    $user = Auth::user();
    return view('principalprofile', ['user' => $user, 'picture' => $picture]);
}

public function principaleditprofile()
{
    $userId = Auth::id();

        $profile = User::findOrFail($userId);

        $picture = profile::where('user_id', $userId)->first(); 

        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'picture' => $picture,
        ];

        return view('principaleditprofile', $data);
}


    public function approvedpayment()
    {
        $students = register_form::where('status', 'approved')->get();
        $payments = payment_form::where('status', 'approved')->get();
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first();

        return view('approvedpayment', compact('students', 'payments', 'picture'));
    }

    public function sectioning()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        $students = register_form::all();
        $payments = payment_form::all();

        $assignedStudentIds = assign::pluck('class_id')->toArray();

        $unassignedStudents = $students->reject(function ($student) use ($assignedStudentIds) {
            return in_array($student->id, $assignedStudentIds);
        });

        return view('sectioning', [
            'students' => $unassignedStudents,
            'payments' => $payments,
            'picture' => $picture
        ]);
    }

    public function assigning()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        $students = register_form::all();
        $payments = payment_form::all();
        $classes = classes::all();

        return view('assigning', [
            'students' => $students,
            'payments' => $payments,
            'classes' => $classes,
            'picture' => $picture
        ]);
    }

    public function section()
    {
        $students = register_form::all();
        $payments = payment_form::all();
        $classes = classes::all();

        return view('section', [
            'students' => $students,
            'payments' => $payments,
            'classes' => $classes
        ]);
    }

    public function proofofpayment()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first();
        $students = register_form::all();
        $payments = payment_form::all();

        return view('cashierstudentfee', [
            'students' => $students,
            'payments' => $payments,
            'picture' => $picture
        ]);
    }

    //admin
    public function admin()
    {
        return view('admin');
    }

    public function adminmanageclassload()
    {
        return view('adminmanageclassload');
    }
    public function adminstudent()
    {
        return view('adminstudent');
    }

    public function adminimportuser()
    {
        return view('adminimportuser');
    }

        public function adminreport()
    {
        $totalStudents = register_form::count();

        $totalAccounting = User::where('role', 'Accounting')->count();
        $totalPrincipal = User::where('role', 'Principal')->count();
        $totalTeachers = User::where('role', 'Teacher')->count();
        $totalCashiers = User::where('role', 'Cashier')->count();
        $record = User::where('role', 'Record')->count();

        $students = register_form::all(); 

        return view('adminreport', compact('totalStudents', 'record', 'totalAccounting', 'totalPrincipal', 'totalTeachers', 'totalCashiers', 'students'));
    }

    public function adminusers()
    {
        $account = User::all();
        return view('adminusers', compact('account'));
    }
    public function updateusers($id)
    {
        $user = User::findOrFail($id); // Get the user
        return view("updateusers", compact('user')); // Pass user data to the view
    }

    public function adminnotification()
    {
        return view('adminnotification');
    }

    public function adminprofile()
    {
        return view('adminprofile');
    }

    public function oldstudent()
    {
        return view('oldstudent');
    }

    public function oldstudentdashboard()
    {
        $userId = Auth::id();
    
      
        $profile = register_form::where('user_id', $userId)->firstOrFail();
        

        $picture = profile::where('user_id', $userId)->first(); 
    
       
        $level = payment_form::where('payment_id', $profile->id)->first();
    
    
        $data = [
            'title' => 'Student Profile',
            'profile' => $profile,
            'level' => $level,
            'picture' => $picture 
        ];
    
        return view('oldstudentdashboard', $data);
    }

    public function oldstudentaddress()
    {
    $registerFormId = session('register_form_id');
    $registerForm = \App\Models\register_form::find($registerFormId);

    if (!$registerForm) {
        return redirect()->route('oldstudentaddress')->withErrors(['error' => 'Register form not found.']);
    }

    return view('oldstudentaddress', compact('registerForm'));
    }

    public function oldstudentprevious()
    {
        $registerFormId = session('register_form_id');
        $registerForm = \App\Models\register_form::find($registerFormId);

        if (!$registerForm) {
            return redirect()->route('oldstudentprevious')->withErrors(['error' => 'Register form not found.']);
    }

    return view('oldstudentprevious', compact('registerForm'));
    }

    public function oldstudentdocuments()
    {
        return view('oldstudentdocuments');
    }

    public function oldstudentpayment()
    {
        $user = Auth::user();
    
        if (!$user) {
            return redirect('/oldstudentenrollment')->with('error', 'User not authenticated.');
        }
    
        $registerForm = \App\Models\register_form::where('user_id', $user->id)->first();
    
        if (!$registerForm) {
            return redirect('/oldstudentenrollment')->with('error', 'Register form not found.');
        }

        session(['register_form_id' => $registerForm->id]);
    
        $payment = \App\Models\payment_form::where('payment_id', $registerForm->id)->get();
    
        return view('oldstudentpayment', compact('payment', 'registerForm'));
    }

        public function oldstudentassessment(Request $request)
    {
        $schoolYears = assessment::select('school_year')->distinct()->pluck('school_year');

        if (Auth::check()) {
            $userId = Auth::user()->id;
            Log::info("Authenticated User ID: " . $userId); // Log the user ID
        } else {
            Log::info("No user is authenticated.");
        }
    
        $user = Auth::user();
        $paymentId = $user ? $user->payment_id : null; 
        $picture = Profile::where('user_id', $userId)->first();
   
        $userPayment = payment_form::where('payment_id', $paymentId)->first();
        $authGradeLevel = $userPayment ? strtolower(str_replace(' ', '', trim($userPayment->level))) : null;

        $assessments = assessment::where('status', 'Published');

        if ($request->has('school_year') && $request->school_year !== '') {
            $assessments = $assessments->where('school_year', $request->school_year);
        }

        if ($authGradeLevel) {
            $assessments = $assessments->where('grade_level', '=', $authGradeLevel); // Exact match
        }

        $assessments = $assessments->get();

        return view('oldstudentassessment', compact('assessments', 'schoolYears', 'authGradeLevel' , 'picture'));
    }


        public function oldstudentenrollment()
        {

            if (Auth::check()) {
                $userId = Auth::user()->id;
                Log::info("Authenticated User ID: " . $userId); // Log the user ID
            } else {
                Log::info("No user is authenticated.");
            }

            if (!Auth::check()) {
                return redirect()->route('login');
            }
        
            $user = Auth::user();
        
            $registerForm = register_form::where('user_id', $user->id)->first();
        
            if (!$registerForm) {
                return redirect()->route('/oldstudentenrollment')->with('error', 'No registration form found.');
            }
        
            $registerFormId = $registerForm->id;
        
            $studentDetail = studentdetails::where('details_id', $registerFormId)->first();
            $address = address::where('address_id', $registerFormId)->first();
            $payment = payment_form::where('payment_id', $registerFormId)->first();
            $previousSchool = previous_school::where('school_id', $registerFormId)->first();
            $requiredDocs = required_docs::where('required_id', $registerFormId)->first();
            $assign = assign::where('class_id', $registerFormId)->first();
          $picture = Profile::where('user_id', $userId)->first();
  
            $allCompleted = true;
        
            $paymentStatus = $payment && is_object($payment) ? $payment->status : null;
            if ($paymentStatus !== 'approved') {
                $allCompleted = false;
            }

            $detailsStatus = $studentDetail && is_object($studentDetail) ? $studentDetail->status : null;
            if ($detailsStatus !== 'approved') {
                $allCompleted = false;
            }

            $addressStatus = $address && is_object($address) ? $address->status : null;
            if ($addressStatus !== 'approved') {
                $allCompleted = false;
            }

            $previousStatus = $previousSchool && is_object($previousSchool) ? $previousSchool->status : null;
            if ($previousStatus !== 'approved') {
                $allCompleted = false;
            }

            $requiredStatus = $requiredDocs && is_object($requiredDocs) ? $requiredDocs->status : null;
            if ($requiredStatus !== 'approved') {
                $allCompleted = false;
            }

            $assignStatus = $assign && is_object($assign) ? $assign->status : null;
            if ($assignStatus !== 'assigned') {
                $allCompleted = false;
            }
        
            $address_id = $address ? $address->address_id : null;
            $details_id = $studentDetail ? $studentDetail->details_id : null;
            $school_id  = $previousSchool ? $previousSchool->school_id : null;
            $required_id = $requiredDocs ? $requiredDocs->required_id : null;
            $payment_id = $payment ? $payment->payment_id : null;
            $class_id = $assign ? $assign->class_id : null;
        
            return view('oldstudentenrollment', compact(
                'allCompleted', 
                'detailsStatus', 
                'addressStatus', 
                'previousStatus', 
                'paymentStatus', 
                'requiredStatus', 
                'assignStatus', 
                'registerFormId', 
                'registerForm',  
                'address_id',
                'details_id',
                'school_id',
                'required_id',
                'payment_id',
                'class_id',
                'picture'
            ));
        }

    public function oldstudentupdatedetails()
    {
        $user = Auth::user();

        if (!$user || !$user->details_id) {
            return redirect('/oldstudentenrollment')->with('error', 'Details not found.');
        }

        $details = studentdetails::findOrFail($user->details_id);

        return view('oldstudentupdatedetails', compact('details'));
    }
    public function oldstudentupdateaddress()
    {
        $user = Auth::user();
        if (!$user || !$user->address_id) {
            return redirect('/oldstudentenrollment')->with('error', 'Details not found.');
        }
        $address = address::findOrFail($user->address_id);

        return view('oldstudentupdateaddress', compact('address'));
    }

    public function oldstudentupdateprevious()
    {
        $user = Auth::user();

        if (!$user || !$user->address_id) {
            return redirect('/oldstudentenrollment')->with('error', 'Details not found.');
        }

        $previous = previous_school::findOrFail($user->address_id);

        return view('oldstudentupdateprevious', compact('previous'));
    }

    public function oldstudentupdatedocuments()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/oldstudentenrollment')->with('error', 'User not authenticated.');
        }

        $registerForm = \App\Models\register_form::where('user_id', $user->id)->first();

        if (!$registerForm) {
            return redirect('/oldstudentenrollment')->with('error', 'Register form not found.');
        }

        $docs = \App\Models\required_docs::where('required_id', $registerForm->id)->get();

        return view('oldstudentupdatedocuments', compact('docs', 'registerForm'));
    }




    public function oldstudentgrades()
    {
        $userId = Auth::id();
        $user = Auth::user();
        $userName = trim("{$user->firstname} {$user->middlename} {$user->lastname}");
    
        $grades = Grade::where('fullname', $userName)
                       ->where('status', 'approved')
                       ->get(['subject', 'edp_code', 'section', '1st_quarter', '2nd_quarter', '3rd_quarter', '4th_quarter', 'overall_grade', 'grade_id']);
    
        $gradesApproved = $grades->isNotEmpty();
        $gradeId = $grades->first()->grade_id ?? null;
    
        $coreId = null;
        if ($gradeId) {
            $coreId = corevalues::where('section', $grades->first()->section)->value('core_id'); 
        }
    
        $attendanceId = null;
        if ($userName) {
            $attendanceId = Attendance::where('fullname', $userName)->value('attendance_id'); 
        }
        $picture = Profile::where('user_id', $userId)->first();
   
    
        return view('oldstudentgrades', [
            'grades' => $grades,
            'gradesApproved' => $gradesApproved,
            'studentId' => $userId,
            'gradeId' => $gradeId,
            'coreId' => $coreId,
            'attendanceId' => $attendanceId,
            'picture' => $picture
        ]);
    }

    public function updatedetails()
    {
        $user = Auth::user();

        if (!$user || !$user->details_id) {
            return redirect('/enrollmentstep')->with('error', 'Details not found.');
        }

        $details = studentdetails::findOrFail($user->details_id);

        return view('updatedetails', compact('details'));
    }
    public function updateaddress()
    {

        $user = Auth::user();


        if (!$user || !$user->address_id) {
            return redirect('/enrollmentstep')->with('error', 'Details not found.');
        }


        $address = address::findOrFail($user->address_id);

        return view('updateaddress', compact('address'));
    }
    public function updatedocuments()
    {
        $user = Auth::user();

        if (!$user || !$user->required_id) {
            return redirect('/enrollmentstep')->with('error', 'Details not found.');
        }

        $docs = required_docs::findOrFail($user->required_id);

        return view('updatedocuments', compact('docs'));
    }
    public function updateschool()
    {
        $user = Auth::user();


        if (!$user || !$user->school_id) {
            return redirect('/enrollmentstep')->with('error', 'Details not found.');
        }


        $school = previous_school::findOrFail($user->school_id);

        return view('updateschool', compact('school'));
    }
    public function principalteacher()
    {
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        return view('principalteacher', compact('picture'));
        
    }

    public function createsection()
    {
      
        $userId = Auth::id();
        $picture = Profile::where('user_id', $userId)->first(); 
        $sections = section::all();
    
        return view('createsection', compact('sections', 'picture'));
    }

    public function forgotpassword()
    {
        return view('forgotpassword');
    }
    public function resetpassword()
    {
        return view('resetpassword');
    }

    public function teacherdisplaygrade(Request $request, $edpcode)
{
    $userId = Auth::id();
    $picture = Profile::where('user_id', $userId)->first(); 
    $grades = Grade::where('edp_code', $edpcode)->get();

    if ($grades->isEmpty()) {
        return redirect()->back()->withErrors(['No grades found for the given EDP code.']);
    }

     return view('teacherdisplaygrade', [
        'grades' => $grades,
        'edpcode' => $edpcode,
        'picture' => $picture
    ]);
}

public function oldstudentupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); // Log the user ID
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('oldstudentupdateprofile', compact('profile', 'picture'));
}

public function recordupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); // Log the user ID
    } else {
        Log::info("No user is authenticated.");
    }
    $picture = Profile::where('user_id', $userId)->first(); 

    return view('recordupdateprofile', compact('picture'));
}

public function studentupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); 
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('studentupdateprofile', compact('profile', 'picture'));
}

public function principalupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); 
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('principalupdateprofile', compact('profile', 'picture'));
}

public function accountingupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); 
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('accountingupdateprofile', compact('profile', 'picture'));
}


public function teacherupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); 
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('teacherupdateprofile', compact('profile', 'picture'));
}


public function cashierupdateprofile()
{
    if (Auth::check()) {
        $userId = Auth::user()->id;
        Log::info("Authenticated User ID: " . $userId); 
    } else {
        Log::info("No user is authenticated.");
    }

    $picture = Profile::where('user_id', $userId)->first();
    $profile = profile::all();

    return view('cashierupdateprofile', compact('profile', 'picture'));
}

public function totalstudent()
{
    $oldStudents = User::where('role', 'Oldstudent')->get();
    $newStudents = User::where('role', 'Newstudent')->get();

    return view('totalstudent', compact('oldStudents', 'newStudents'));
}
public function totalteacher()
{
    $teachers = User::where('role', 'Teacher')->get();

    return view('totalteacher', compact('teachers'));
}
public function totalprincipal()
{
    $principals = User::where('role', 'Principal')->get();
    $totalPrincipal = $principals->count();
    return view('totalprincipal', compact('principals', 'totalPrincipal'));
}

public function totalcashier()
{
    $cashiers = User::where('role', 'Cashier')->get();
    $totalCashiers = $cashiers->count();

    return view('totalcashier', compact('cashiers', 'totalCashiers'));
}

public function totalaccounting()
{
    $accountingStaff = User::where('role', 'Accounting')->get();
    $totalAccounting = $accountingStaff->count();

    return view('totalaccounting', compact('accountingStaff', 'totalAccounting'));
}

public function totalrecord()
{
    $totalRecord = User::where('role', 'Record')->get();
    $countRecord = $totalRecord->count();

    return view('totalrecord', compact('totalRecord', 'countRecord'));
}
}
