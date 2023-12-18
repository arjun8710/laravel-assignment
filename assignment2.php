
// Creating  migrations for the students and marks tables:
php artisan make:migration create_students_table
php artisan make:migration create_marks_table 

//create_students_table.php
// database/migrations/create_students_table.php
public function up()
{
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->string('student_name');
        $table->string('standard');
        $table->timestamps();
    });
}
//create_marks_table.php
// database/migrations/create_marks_table.php
public function up()
{
    Schema::create('marks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained();
        $table->string('subject_name');
        $table->integer('marks');
        $table->date('test_date');
        $table->timestamps();
    });
}

//creating Student and Mark Models:
php artisan make:model Student
php artisan make:model Mark

// defining relationships in the models.
// app/Models/Student.php
class Student extends Model
{
    protected $fillable = ['student_name', 'standard'];
    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}

// app/Models/Mark.php
class Mark extends Model
{
    protected $fillable = ['subject_name', 'marks', 'test_date'];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}


//Createing controllers for handling the API endpoints:
php artisan make:controller StudentController
//StudentController.php
// app/Http/Controllers/StudentController.php
use App\Models\Student;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $standard = $request->input('standard');
        $students = $standard ? Student::where('standard', $standard)->paginate(10) :       Student::paginate(10);
        return response()->json($students);
    }

    public function fetchResults()
    {
        $students = Student::with('marks')->get();
        $results = [];
        foreach ($students as $student) {
            $totalMarks = $student->marks->sum('marks');
            $percentage = ($totalMarks / (count($student->marks) * 100)) * 100;

            if ($percentage < 35) {
                $result = 'Fail';
            } elseif ($percentage < 60) {
                $result = 'Second class';
            } elseif ($percentage < 85) {
                $result = 'First class';
            } else {
                $result = 'Distinction';
            }
            $results[] = [
                'student_name' => $student->student_name,
                'percentage' => $percentage,
                'result' => $result,
            ];
        }
        return response()->json($results);
    }
}

//defining route :
use App\Http\Controllers\StudentController;
Route::get('/students', [StudentController::class, 'index']);
Route::get('/fetch_results', [StudentController::class, 'fetchResults']);



