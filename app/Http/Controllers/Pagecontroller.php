<?php

namespace App\Http\Controllers;

use App\Models\address;
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
        return view('studentdashboard');
    }
    public function studentclassload()
{
    $userId = Auth::id(); 

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

    $assignedClasses = assign::where('class_id', $registerForm->id)->get();

    $student = $registerForm;

    $proof = payment_form::where('payment_id', $registerForm->id)->first();

    return view('oldstudentclassload', [
        'assignedClasses' => $assignedClasses,
        'student' => $student,
        'proof' => $proof,
    ]);
}

    public function studentprofile()
    {
        return view('studentprofile');
    }

    public function oldstudentprofile()
    {
        return view('oldstudentprofile');
    }

    public function enrollmentStep()
{
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

    return view('enrollmentstep', compact('details', 'address', 'previousSchool', 'requiredDocs', 'payment', 'assignStatus'));
}

public function studentgrades()
{
    $userId = Auth::id();
    
    // Get the authenticated user
    $user = Auth::user();
    
    // Construct the full name
    $userName = trim("{$user->firstname} {$user->middlename} {$user->lastname}");

    // Retrieve grades for the authenticated student based on their fullname and status
    $grades = Grade::where('fullname', $userName)
                   ->where('status', 'approved')
                   ->get(['subject', 'edp_code', 'section', '1st_quarter', '2nd_quarter', '3rd_quarter', '4th_quarter', 'overall_grade']);

    // Check if there are any approved grades
    $gradesApproved = $grades->isNotEmpty();

    return view('studentgrades', [
        'grades' => $grades,
        'gradesApproved' => $gradesApproved,
    ]);
}

public function studentassessment(Request $request)
{
    $schoolYears = assessment::select('school_year')->distinct()->pluck('school_year');

    $user = Auth::user();
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
    return view('studentassessment', compact('assessments', 'schoolYears', 'authGradeLevel'));
}

    //teacher 
    public function teacher()
    {
        return view('teacher');
    }
    public function teachernotification()
    {
        return view('teachernotification');
    }
    public function teacherprofile()
    {
        $user = Auth::user();
        return view('teacherprofile', ['user' => $user]);
    }

    public function teacherclassload()
    {
        // Get the authenticated user's ID
        $authUserId = Auth::id();
    
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
            'proofs' => $proofs 
        ]);
    }


    
    public function teacherattendance()
    {
        // Get the authenticated user's ID
        $authUserId = Auth::id();
    
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
            'proofs' => $proofs 
        ]);
    }

    public function teachercorevalue()
    {
        // Get the authenticated user's ID
        $authUserId = Auth::id();
    
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
            'proofs' => $proofs 
        ]);
    }


    //principal
    public function principal()
    {
        return view('principal');
    }

    public function principalclassload()
    {
        $section = section::all();
        $class = classes::all();
        $teachers = teacher::all();
        return view('principalclassload', compact('class', 'teachers', 'section'));
    }
    
    public function showEvaluateGrades(Request $request)
    {
        $quarterSettings = QuarterSettings::first();
    
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
        
        $grades = grade::where('status', 'pending')->get()->unique('subject');
    
        return view('submittedgrades', [
            'quartersEnabled' => $quartersEnabled,
            'quartersStatus' => $quartersStatus,
            'quarterSettings' => $quarterSettings,
            'assigns' => $assigns,
            'grades' => $grades,
        ]);
    }

    public function publishgrade()
    {
        return view('publishgrade');
    }

    //accounting
    public function accounting()
    {
        return view('accounting');
    }

    public function accountingassessment()
    {
        $assessments = assessment::all(); 
        return view('accountingassessment', compact('assessments'));
    }

    public function createassessment()
    {
        return view('createassessment');
    }
    public function accountingprofile()
    {
        $user = Auth::user();
        return view('accountingprofile', ['user' => $user]);
    }

    //record
    public function record()
    {
        $pendingCount = register_form::where('status', 'pending')->count();
        $approvedCount = register_form::where('status', 'approved')->count();

        $students = studentdetails::all(); 

        return view('record', compact('students', 'pendingCount', 'approvedCount'));
    }

    public function recordprofile()
    {
        $user = Auth::user();
        return view('recordprofile', ['user' => $user]);
    }

    public function studententries()
    {

        $studentDetails = studentdetails::all();
        $previousSchools = previous_school::all();
        $addresses = address::all();
        $requiredDocs = required_docs::all();

        // Pass all datasets to the view
        return view('studententries', compact('studentDetails', 'previousSchools', 'addresses', 'requiredDocs'));
    }

    public function showdetails()
    {
        return view('showdetails');
    }

    public function studentapplicant()
    {
        $account = register_form::where('status', register_form::STATUS_PENDING)->get();
        return view('studentapplicant', compact('account'));
    }

    public function approvedaccount()
    {
        $account = register_form::where('status', 'approved')->get();
        return view('approvedaccount', compact('account'));
    }
    public function  recordapproval()
    {
        $account = register_form::all();
        return view(' recordapproval', compact('account'));
    }

    //cashier 
    public function cashier()
    {
        $pendingCount = payment_form::where('status', 'pending')->count();
        $approvedCount = payment_form::where('status', 'approved')->count();
        $students = studentdetails::all();
        return view('cashier', compact('students', 'pendingCount', 'approvedCount'));
    }
    public function cashieraddfee()
    {
        return view('cashieraddfee');
    }
    public function cashierstudentfee()
{
    // Fetch all students
    $students = register_form::all();
    
    // Fetch all payments
    $payments = payment_form::all();

    // Pass both students and payments to the view
    return view('cashierstudentfee', [
        'students' => $students,
        'payments' => $payments,
    ]);
}

public function cashierprofile()
{
    $user = Auth::user();
    return view('cashierprofile', ['user' => $user]);
}

public function principalassessment()
{
    $assessments = Assessment::all(); 
    return view('principalassessment', compact('assessments'));
}

public function principaleditassessment()
{
    return view('principaleditassessment');
}

public function principalprofile()
{
    $user = Auth::user();
    return view('principalprofile', ['user' => $user]);
}


    public function approvedpayment()
    {
        $students = register_form::where('status', 'approved')->get();
        $payments = payment_form::where('status', 'approved')->get();

        return view('approvedpayment', compact('students', 'payments'));
    }

    public function sectioning()
    {
        $students = register_form::all();
        $payments = payment_form::all();

        $assignedStudentIds = assign::pluck('class_id')->toArray();

        $unassignedStudents = $students->reject(function ($student) use ($assignedStudentIds) {
            return in_array($student->id, $assignedStudentIds);
        });

        return view('sectioning', [
            'students' => $unassignedStudents,
            'payments' => $payments,
        ]);
    }

    public function assigning()
    {
        $students = register_form::all();
        $payments = payment_form::all();
        $classes = classes::all();

        return view('assigning', [
            'students' => $students,
            'payments' => $payments,
            'classes' => $classes
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
        $students = register_form::all();
        $payments = payment_form::all();

        return view('cashierstudentfee', [
            'students' => $students,
            'payments' => $payments,
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

        $totalAccounting = User::where('role', 'accountant')->count();
        $totalPrincipal = User::where('role', 'principal')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalCashiers = User::where('role', 'cashier')->count();

        $students = register_form::all(); 

        return view('adminreport', compact('totalStudents', 'totalAccounting', 'totalPrincipal', 'totalTeachers', 'totalCashiers', 'students'));
    }

    public function adminusers()
    {
        $account = User::all();
        return view('adminusers', compact('account'));
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
        return view('oldstudentdashboard');
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

        $user = Auth::user();
        $paymentId = $user ? $user->payment_id : null; 

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

        return view('oldstudentassessment', compact('assessments', 'schoolYears', 'authGradeLevel'));
    }


        public function oldstudentenrollment()
        {
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
                'class_id'
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
            $coreId = corevalues::where('section', $grades->first()->section)->value('core_id'); // Adjust based on your schema
        }
    
        $attendanceId = null;
        if ($userName) {
            $attendanceId = Attendance::where('fullname', $userName)->value('attendance_id'); // Using fullname to fetch attendance ID
        }
    
        return view('oldstudentgrades', [
            'grades' => $grades,
            'gradesApproved' => $gradesApproved,
            'studentId' => $userId,
            'gradeId' => $gradeId,
            'coreId' => $coreId,
            'attendanceId' => $attendanceId,
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
        return view('principalteacher');
    }

    public function createsection()
    {
      
        $sections = section::all();
    
        return view('createsection', compact('sections'));
    }
}
