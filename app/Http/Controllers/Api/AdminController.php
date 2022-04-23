<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class AdminController extends Controller
{
    public function login(Request $request)
    {

        $validator = validator::make($request->all(), [
            'user_name' => 'required', //username
            'password' => 'required',
            //'user_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // $user_type = request('user_type');
        $email = $request->input('user_name');
        // $password =$request->input('password');

        // return response()->json(['da'=>$num_of_rows]);

        $details = DB::table('users')
            ->select('id', 'user_name', 'user_id', 'password', 'status')
            ->where('status', 1)
            ->where('user_name', $email)
            ->get();

        // return $details;

        $num_of_rows = count($details);

//return $details;

        if ($num_of_rows == 0) { // staff user not exists
            return response()->json(['success' => false, 'status_code' => 0, 'error' => ['error_code' => '401', 'error_message' => 'Authentication failed']], 401);

        } else if ($num_of_rows > 1) {
            return response()->json(['success' => false, 'status_code' => 1, 'error' => ['error_code' => '401', 'error_message' => 'Please contact College Admin']], 401);
        } else if ($num_of_rows == 1) {

            $userDetails = DB::table('faculty as f')
                ->where('f.faculty_id', $details[0]->user_id)
                ->first();

            return response()->json(['sucess' => true, 'message' => 'Login sucessfully', 'userDetails' => $userDetails]);

        }

    }
    /**
     * endpoint for get all departments
     */

    public function getAllDepartments()
    {

        $dep = DB::table('departments')
            ->get();
        if ($dep) {
            return response()->json(['departments' => $dep], 200);
        } else {
            return response()->json([" No Data Found"], 200);
        }

    }

    /**
     * endpoint for get all courses
     */

    public function getAllCourses($dep_id)
    {

        $dep = DB::table('course as c')
            ->leftjoin('departments as d', 'd.dep_id', 'c.dep_id')
            ->where('c.dep_id', $dep_id)
            ->select('c.*', 'd.dep_name')
            ->get();
        if ($dep) {
            return response()->json(['courses' => $dep], 200);
        } else {
            return response()->json([" No Data Found"], 200);
        }

    }

    public function addQuestionPaper(Request $request)
    {

        $validator = validator::make($request->all(), [

            'dep_id' => 'required',
            'course_id' => 'required',
            'semester' => 'required',
            'question' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $dep_id = $request->input('dep_id');
        $course_id = $request->input('course_id');
        $semester = $request->input('semester');
        $question = $request->input('question');

        $host = $_SERVER['HTTP_HOST'];
        $url = explode(".", $host);
        $root = $url[0];
        $file = $request->file('path_url1');
        // $name = time() . $file->getClientOriginalName();
        // $file_name = $file->getClientOriginalName();
        $status = 1;
        $path_url1 = Storage::disk('suporting_files')->putFile('/question/' . $root . '/questionbank', $request->file('question'));

        $dig_file = DB::table('questions')
            ->insertGetId([
                'dep_id' => $dep_id,
                'course_id' => $course_id,
                'semester' => $semester,
                'question_url' => $path_url1,
                'status' => $status,
            ]);

        $applicant = DB::table('questions')
            ->where('id', $dig_file)
            ->first();

        if ($dig_file) {
            return response()->json(['success' => true,
                'message' => 'File uploaded successfully',
                'data' => $applicant,
                'url1' => url('SupportingFiles/' . $path_url1),
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'File uploading failed']);
        }
    }

    /**
     * getall quetion paper
     */

    public function getAllQuestion()
    {

        $question = DB::table('questions as q')
            ->leftjoin('departments as d', 'd.dep_id', 'q.dep_id')
            ->leftjoin('course as c', 'c.id', 'q.course_id')
            ->where('q.status', 1)
            ->select('q.*', 'd.dep_name', 'c.course_name')
            ->get();
            foreach($question as $q)
            {
                if($q->question_url != null)
                {
                    $q->question_url = url('SupportingFiles/' . $q->question_url);
                }
            }

        if ($question) {
            return response()->json(['questions' => $question], 200);
        } else {
            return response()->json(['questions' => []], 200);
        }
    }


/***
 * get students questions
 */



    public function getStudentQuestion(Request $request)
    {

            $validator = validator::make($request->all(), [
    
                'dep_id' => 'required',
                'course_id' => 'required',
                'semester' => 'required',
                
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            $dep_id = $request->input('dep_id');
            $course_id = $request->input('course_id');
            $semester = $request->input('semester');
           

        $question = DB::table('questions as q')
             ->where('q.dep_id',$dep_id)
             ->where('q.course_id',$course_id)
             ->where('q.semester',$semester)
            ->leftjoin('departments as d', 'd.dep_id', 'q.dep_id')
            ->leftjoin('course as c', 'c.id', 'q.course_id')
            ->where('q.status', 2)
            ->select('q.*', 'd.dep_name', 'c.course_name')
            ->get();
            foreach($question as $q)
            {
                if($q->question_url != null)
                {
                    $q->question_url = url('SupportingFiles/' . $q->question_url);
                }
            }

        if ($question) {
            return response()->json(['questions' => $question], 200);
        } else {
            return response()->json(['questions' => []], 200);
        }
    }

/**
 * endpoint for accept question
 */


    public function accptOrReject(Request $request)
    {

            $validator = validator::make($request->all(), [
    
                'id' => 'required',
                'status' => 'required',
                
                
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
    
            $id = $request->input('id');
            $status = $request->input('status');

      if($status == 2)
{

            $dig_file = DB::table('questions')
            ->where('id',$id)
            ->update([
                'status' => $status,
            ]);


            if ($dig_file) {
                return response()->json(['success' => true,
                    'message' => 'Admin Accept the file'           
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'File uploading failed']);
            }
           
        }
        else if($status == 0)
  {
  
              $dig_file = DB::table('questions')
              ->where('id',$id)
              ->update([
                  'status' => $status,
              ]);
  
  
              if ($dig_file) {
                  return response()->json(['success' => true,
                      'message' => 'Admin Reject the file'           
                  ]);
              } else {
                  return response()->json(['success' => false, 'message' => 'File uploading failed']);
              }
             
          }
    }

}
