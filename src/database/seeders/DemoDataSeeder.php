<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\StaffPosition;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    protected array $indonesianFirstNames = [
        'Laki-laki' => ['Ahmad', 'Budi', 'Dedi', 'Eko', 'Fajar', 'Hendra', 'Indra', 'Joko', 'Krisna', 'Lukman', 'Muhamad', 'Nico', 'Putra', 'Rizki', 'Satria', 'Toni', 'Umam', 'Vino', 'Yoga', 'Zainal'],
        'Perempuan' => ['Anisa', 'Bella', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gusti', 'Hani', 'Intan', 'Januari', 'Kartika', 'Lina', 'Maria', 'Nita', 'Olivia', 'Putri', 'Queen', 'Rina', 'Sari', 'Tika'],
    ];

    protected array $indonesianLastNames = ['Permana', 'Santoso', 'Wijaya', 'Kusuma', 'Saputra', 'Pratama', 'Susanto', 'Hermawan', 'Nugraha', 'Setiawan', 'Rahman', 'Fauzi', 'Hakim', 'Irawan', 'Jaya', 'Kurniawan', 'Maulana', 'Novianto', 'Prawira', 'Rahmadi'];

    public function run(): void
    {
        $this->command->info('🌱 Starting demo data seeding...');

        $academicYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();

        $staffPositions = StaffPosition::all();
        $defaultPosition = $staffPositions->first();

        $classrooms = $this->createClassrooms($academicYear);
        $subjects = $this->createSubjects();
        $employees = $this->createEmployees($defaultPosition);
        $teachers = $this->createTeachers($employees);
        $students = $this->createStudents(50);

        $this->assignStudentsToClassrooms($students, $classrooms);
        $this->assignSubjectsToClassrooms($classrooms, $subjects);
        $this->createGrades($students, $subjects, $classrooms);
        $this->createStudentAttendances($students, $classrooms);
        $this->createPayments($students, $classrooms);
        $this->createWhatsAppConversations($students);
        $this->createTeacherClassrooms($teachers, $classrooms);

        $this->command->info('✅ Demo data seeded successfully!');
    }

    protected function createClassrooms($academicYear): array
    {
        $classrooms = [];
        $classTypes = [
            ['name' => 'Kelas 1 Baghdad', 'type' => 'I Baghdad'],
            ['name' => 'Kelas 1 Madinah', 'type' => 'I Madinah'],
            ['name' => 'Kelas 1 Mekkah', 'type' => 'I Mekkah'],
            ['name' => 'Kelas 2 Alexandria', 'type' => 'II Alexandria'],
            ['name' => 'Kelas 2 Kairo', 'type' => 'II Kairo'],
            ['name' => 'Kelas 2 Yerussalem', 'type' => 'II Yerussalem'],
            ['name' => 'Kelas 3 Cordoba', 'type' => 'III Cordoba'],
            ['name' => 'Kelas 3 Damaskus', 'type' => 'III Damaskus'],
            ['name' => 'Kelas 4 Amman', 'type' => 'IV Amman'],
            ['name' => 'Kelas 4 Bukhara', 'type' => 'IV Bukhara'],
            ['name' => 'Kelas 5 Al-Quds', 'type' => 'V Al-Quds'],
            ['name' => 'Kelas 5 Andalusia', 'type' => 'V Andalusia'],
            ['name' => 'Kelas 6 Abu Dhabi', 'type' => 'VI Abu Dhabi'],
            ['name' => 'Kelas 6 Gaza', 'type' => 'VI Gaza'],
        ];

        foreach ($classTypes as $class) {
            $classroom = Classroom::where('classroom_type', $class['type'])
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if ($classroom) {
                $classroom->update([
                    'name' => $class['name'],
                    'slug' => Str::slug($class['name']),
                    'academic_year_id' => $academicYear->id,
                ]);
            } else {
                $classroom = Classroom::create([
                    'name' => $class['name'],
                    'classroom_type' => $class['type'],
                    'slug' => Str::slug($class['name']),
                    'academic_year_id' => $academicYear->id,
                ]);
            }

            $classrooms[] = $classroom;
        }

        $this->command->info('   📚 Created '.count($classrooms).' classrooms');

        return $classrooms;
    }

    protected function createSubjects(): array
    {
        $subjects = [];
        $subjectNames = [
            ['name' => 'Pendidikan Agama Islam', 'slug' => 'pendidikan-agama-islam'],
            ['name' => 'Pendidikan Kewarganegaraan', 'slug' => 'pendidikan-kewarganegaraan'],
            ['name' => 'Bahasa Indonesia', 'slug' => 'bahasa-indonesia'],
            ['name' => 'Matematika', 'slug' => 'matematika'],
            ['name' => 'IPA', 'slug' => 'ipa'],
            ['name' => 'IPS', 'slug' => 'ips'],
            ['name' => 'Seni Budaya', 'slug' => 'seni-budaya'],
            ['name' => 'Pendidikan Jasmani', 'slug' => 'pendidikan-jasmani'],
            ['name' => 'Bahasa Inggris', 'slug' => 'bahasa-inggris'],
            ['name' => 'TIK', 'slug' => 'tik'],
        ];

        foreach ($subjectNames as $subject) {
            $subjects[] = Subject::firstOrCreate(
                ['slug' => $subject['slug']],
                ['name' => $subject['name'], 'slug' => $subject['slug']]
            );
        }

        $this->command->info('   📖 Created '.count($subjects).' subjects');

        return $subjects;
    }

    protected function createEmployees($defaultPosition): array
    {
        $employees = [];
        $employeeData = [
            ['name' => 'Drs. H. Ahmad Wijaya, M.Pd', 'sex' => 'Laki-Laki', 'position' => 'Kepala Sekolah', 'salary' => 15000000],
            ['name' => ' Dra. Siti Rahayu', 'sex' => 'Perempuan', 'position' => 'Waketur Kurikulum', 'salary' => 10000000],
            ['name' => 'H. Budi Santoso, S.Pd', 'sex' => 'Laki-Laki', 'position' => 'Waketur Kesiswaan', 'salary' => 10000000],
            ['name' => ' Dra. Dewi Kusuma', 'sex' => 'Perempuan', 'position' => 'Sekretaris', 'salary' => 6000000],
            ['name' => 'Joko Pramono, S.Pd', 'sex' => 'Laki-Laki', 'position' => 'Tata Usaha', 'salary' => 5000000],
            ['name' => 'Indra Gumilar, S.Si', 'sex' => 'Laki-Laki', 'position' => 'Bendahara', 'salary' => 5500000],
            ['name' => 'Lisa Permata, S.Ak', 'sex' => 'Perempuan', 'position' => 'Keuangan', 'salary' => 5000000],
            ['name' => 'Muhamad Fadli, S.Kom', 'sex' => 'Laki-Laki', 'position' => 'IT Support', 'salary' => 4500000],
            ['name' => 'Nina Hartati, S.Pd', 'sex' => 'Perempuan', 'position' => 'Perpustakaan', 'salary' => 4000000],
            ['name' => 'Putra Pratama', 'sex' => 'Laki-Laki', 'position' => 'Satpam', 'salary' => 3500000],
        ];

        $positions = StaffPosition::whereIn('name', array_column($employeeData, 'position'))->get()->keyBy('name');

        foreach ($employeeData as $index => $emp) {
            $slug = Str::slug($emp['name']);
            $employee = Employee::where('slug', $slug)->first();

            if (! $employee) {
                $position = $positions[$emp['position']] ?? $defaultPosition;
                $employee = Employee::create([
                    'name' => $emp['name'],
                    'sex' => $emp['sex'],
                    'phone' => '08'.str_pad((string) ($index + 1100000000), 10, '0', STR_PAD_LEFT),
                    'nip' => str_pad((string) (198500000 + $index), 18, '0', STR_PAD_LEFT),
                    'nik' => str_pad((string) (3274 .rand(100000000, 999999999)), 16, '0', STR_PAD_LEFT),
                    'staff_position_id' => $position->id,
                    'slug' => $slug,
                    'base_salary' => $emp['salary'],
                    'status' => 1,
                ]);
            } else {
                $employee->update(['base_salary' => $emp['salary'], 'status' => 1]);
            }
            $employees[] = $employee;
        }

        $this->command->info('   👔 Created '.count($employees).' employees');

        return $employees;
    }

    protected function createTeachers(array $employees): array
    {
        $teachers = [];
        $teacherData = [
            ['name' => 'Ustadz H. Muhammad Idris, M.Ag', 'employee_id' => 1],
            ['name' => 'Ustadzah Dra. Hj. Fatimah', 'employee_id' => 2],
            ['name' => 'Pak Hendra Wijaya, S.Pd', 'employee_id' => 3],
            ['name' => 'Bu Ratna Sari, S.Pd', 'employee_id' => 4],
            ['name' => 'Pak Anto Susanto, S.Si', 'employee_id' => 5],
            ['name' => 'Bu Yuni Listiani, S.Pd', 'employee_id' => 6],
            ['name' => 'Pak Reza Fernando, S.Kom', 'employee_id' => 7],
            ['name' => 'Bu Maya Kusuma DewI, S.Pd', 'employee_id' => 8],
        ];

        foreach ($teacherData as $index => $teacher) {
            $slug = Str::slug($teacher['name']);
            $existingTeacher = Teacher::where('slug', $slug)->first();

            if (! $existingTeacher) {
                $emp = $employees[$teacher['employee_id'] - 1] ?? $employees[0];
                $existingTeacher = Teacher::create([
                    'name' => $teacher['name'],
                    'employee_id' => $emp->id,
                    'slug' => $slug,
                ]);
            }
            $teachers[] = $existingTeacher;
        }

        $this->command->info('   👨‍🏫 Created '.count($teachers).' teachers');

        return $teachers;
    }

    protected function createStudents(int $count): array
    {
        $students = [];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $provinces = [
            ['id' => '02', 'name' => 'JAWA BARAT', 'regencies' => [
                ['id' => '0273', 'name' => 'KOTA BANDUNG', 'districts' => ['CL', 'BW', 'SB']],
                ['id' => '0274', 'name' => 'KOTA BEKASI', 'districts' => ['BT', 'BS', 'MS']],
            ]],
            ['id' => '03', 'name' => 'JAWA TENGAH', 'regencies' => [
                ['id' => '0371', 'name' => 'KOTA SEMARANG', 'districts' => ['GJ', 'SS', 'TM']],
            ]],
        ];

        for ($i = 1; $i <= $count; $i++) {
            $gender = $i % 2 === 0 ? 'Laki-laki' : 'Perempuan';
            $firstName = $this->indonesianFirstNames[$gender][array_rand($this->indonesianFirstNames[$gender])];
            $lastName = $this->indonesianLastNames[array_rand($this->indonesianLastNames)];
            $fullName = $firstName.' '.$lastName;
            $nisn = str_pad((string) (1000000000 + $i), 10, '0', STR_PAD_LEFT);
            $slug = Str::slug($fullName).'-'.$i;

            $province = $provinces[array_rand($provinces)];
            $regency = $province['regencies'][array_rand($province['regencies'])];
            $district = $regency['districts'][array_rand($regency['districts'])];

            $existingStudent = Student::where('nisn', $nisn)->first();
            if ($existingStudent) {
                $students[] = $existingStudent;

                continue;
            }

            $student = Student::create([
                'id' => Str::uuid(),
                'name' => $fullName,
                'email' => strtolower(str_replace(' ', '.', $fullName)).$i.'@siswa.school.local',
                'gender' => $gender,
                'birth_place' => $regency['name'],
                'birth_date' => now()->subYears(rand(7, 14))->subDays(rand(0, 365))->format('Y-m-d'),
                'nisn' => $nisn,
                'religion' => $religions[array_rand($religions)],
                'phone' => '08'.str_pad((string) (1200000000 + $i), 10, '0', STR_PAD_LEFT),
                'parent_phone' => '08'.str_pad((string) (1300000000 + $i), 10, '0', STR_PAD_LEFT),
                'parent_email' => 'orangtuassiswa'.$i.'@email.com',
                'father_name' => 'Bpk. '.$lastName,
                'mother_name' => 'Ibu. '.$lastName,
                'father_occupation' => 'Karyawan',
                'mother_occupation' => 'Ibu Rumah Tangga',
                'guardian_name' => 'Bpk. '.$lastName,
                'guardian_occupation' => 'Karyawan',
                'province_id' => $province['id'],
                'regency_id' => $regency['id'],
                'district_id' => $district,
                'village_id' => 'VL',
                'street' => 'Jl. Contoh No. '.rand(1, 100),
                'entry_year' => date('Y'),
                'entry_date' => now()->subYear()->format('Y-07-01'),
                'status' => 'active',
                'slug' => $slug,
            ]);

            $students[] = $student;
        }

        $this->command->info('   🎓 Created '.count($students).' students');

        return $students;
    }

    protected function assignStudentsToClassrooms(array $students, array $classrooms): void
    {
        foreach ($students as $student) {
            $classroom = $classrooms[array_rand($classrooms)];
            $studentId = (string) $student->id;
            $classroomId = (string) $classroom->id;

            $exists = DB::table('student_classrooms')
                ->where('student_id', $studentId)
                ->where('classroom_id', $classroomId)
                ->exists();

            if (! $exists) {
                DB::table('student_classrooms')->insert([
                    'student_id' => $studentId,
                    'classroom_id' => $classroomId,
                    'academic_year_id' => (string) $classroom->academic_year_id,
                    'classroom_type' => $classroom->classroom_type,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            }
        }
        $this->command->info('   📝 Assigned students to classrooms');
    }

    protected function assignSubjectsToClassrooms(array $classrooms, array $subjects): void
    {
        foreach ($classrooms as $classroom) {
            $existingSubjects = DB::table('classroom_subjects')
                ->where('classroom_id', (string) $classroom->id)
                ->pluck('subject_id')
                ->toArray();

            $numToAssign = rand(4, min(6, count($subjects)));
            $shuffledSubjects = $subjects;
            shuffle($shuffledSubjects);
            $selectedSubjects = array_slice($shuffledSubjects, 0, $numToAssign);

            foreach ($selectedSubjects as $subject) {
                if (! in_array($subject->id, $existingSubjects)) {
                    DB::table('classroom_subjects')->insert([
                        'classroom_id' => (string) $classroom->id,
                        'subject_id' => (string) $subject->id,
                    ]);
                }
            }
        }
        $this->command->info('   📗 Assigned subjects to classrooms');
    }

    protected function createGrades(array $students, array $subjects, array $classrooms): void
    {
        $gradeCount = 0;
        $semesters = ['ganjil', 'genap'];
        $academicYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();

        foreach ($students as $student) {
            $classroom = $student->classrooms()->first();
            if (! $classroom) {
                continue;
            }

            $studentSubjects = $classroom->subjects()->get();
            if ($studentSubjects->isEmpty()) {
                continue;
            }

            foreach ($semesters as $semester) {
                foreach ($studentSubjects as $subject) {
                    $existingGrade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('semester', $semester)
                        ->first();

                    if ($existingGrade) {
                        continue;
                    }

                    $baseScore = rand(70, 95);
                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'classroom_id' => $classroom->id,
                        'academic_year_id' => $academicYear?->id,
                        'academic_year' => $academicYear?->name,
                        'semester' => $semester,
                        'score' => $baseScore + rand(-5, 10),
                    ]);
                    $gradeCount++;
                }
            }
        }

        $this->command->info('   📊 Created '.$gradeCount.' grades');
    }

    protected function createStudentAttendances(array $students, array $classrooms): void
    {
        $attendanceCount = 0;
        $statuses = [
            ['hadir', 80],
            ['sakit', 10],
            ['izin', 7],
            ['alpa', 3],
        ];

        foreach ($students as $student) {
            $classroom = $student->classrooms()->first();
            if (! $classroom) {
                continue;
            }

            $existingDates = $student->attendances()
                ->where('classroom_id', $classroom->id)
                ->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->toArray();

            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays(30 - $i)->format('Y-m-d');
                if (in_array($date, $existingDates)) {
                    continue;
                }

                $rand = rand(1, 100);
                $status = 'hadir';
                foreach ($statuses as $s) {
                    if ($rand <= $s[1]) {
                        $status = $s[0];
                        break;
                    }
                    $rand -= $s[1];
                }

                StudentAttendance::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'classroom_type' => 'App\\Models\\Classroom',
                    'date' => $date,
                    'status' => $status,
                ]);
                $attendanceCount++;
            }
        }

        $this->command->info('   ✅ Created '.$attendanceCount.' attendances');
    }

    protected function createPayments(array $students, array $classrooms): void
    {
        $paymentCount = 0;
        $paymentTitles = PaymentTitle::all();

        if ($paymentTitles->isEmpty()) {
            $this->command->info('   💰 No payment titles found, skipping payments');

            return;
        }

        foreach ($students as $index => $student) {
            $classroom = $student->classrooms()->first();

            $existingPayments = Payment::where('student_id', $student->id)->count();
            if ($existingPayments >= $paymentTitles->count()) {
                continue;
            }

            foreach ($paymentTitles as $paymentTitle) {
                $existing = Payment::where('student_id', $student->id)
                    ->where('payment_title_id', $paymentTitle->id)
                    ->first();

                if ($existing) {
                    continue;
                }

                $dueDate = now()->addDays(rand(-30, 60));
                $payment = Payment::create([
                    'order_id' => (string) Str::uuid(),
                    'student_id' => $student->id,
                    'classroom_id' => $classroom?->id,
                    'classroom_type' => $classroom?->classroom_type,
                    'payment_title_id' => $paymentTitle->id,
                    'gross_amount' => $paymentTitle->amount ?? rand(100000, 1500000),
                    'start_date' => now()->subDays(30),
                    'end_date' => $dueDate,
                    'status' => $dueDate < now() ? 'overdue' : 'pending',
                    'payment_type' => $paymentTitle->type ?? 'registration',
                ]);
                $paymentCount++;
            }
        }

        $this->command->info('   💰 Created '.$paymentCount.' payments');
    }

    protected function createWhatsAppConversations(array $students): void
    {
        $admins = User::role(['admin', 'superadmin'])->get();

        foreach ($students as $index => $student) {
            if ($index >= 10) {
                break;
            }

            $phone = '62812'.str_pad((string) (9000000 + $index), 7, '0', STR_PAD_LEFT);
            $assignedAdmin = $admins->isNotEmpty() ? $admins->random() : null;

            $existingConversation = WhatsAppConversation::where('phone_number', $phone)->first();

            if (! $existingConversation) {
                $conversation = WhatsAppConversation::create([
                    'id' => (string) Str::uuid(),
                    'phone_number' => $phone,
                    'profile_name' => 'Orang Tua '.$student->name,
                    'student_id' => $student->id,
                    'assigned_admin_id' => $assignedAdmin?->id,
                    'status' => $index % 3 === 0 ? 'closed' : 'active',
                    'notes' => $index % 2 === 0 ? 'Pertanyaan tentang '.($index % 2 === 0 ? 'pembayaran' : 'sekolah') : null,
                    'message_count' => rand(2, 8),
                    'last_message_at' => now()->subDays(rand(0, 7)),
                ]);

                $this->createWhatsAppMessages($conversation, $assignedAdmin);
            }
        }

        $this->command->info('   💬 Created WhatsApp conversations & messages');
    }

    protected function createWhatsAppMessages($conversation, $admin): void
    {
        $messages = [
            ['parent', 'Assalamualaikum pak/bu, saya ingin bertanya tentang '.($conversation->message_count % 2 === 0 ? 'jadwal pelajaran' : 'pembayaran SPP')],
            ['admin', 'Waalaikumussalam. Dengan siapa ini pak/bu?'],
            ['parent', 'Ini orang tua '.($conversation->student->name ?? 'siswa')],
            ['admin', 'Baikpak/bu. '.($conversation->message_count % 2 === 0 ? 'Jadwal pelajaran bisa dilihat di dashboard sekolah' : 'Pembayaran SPP bulan ini sudah diterima, thank you')],
            ['parent', 'Baik terima kasih pak/bu'],
        ];

        foreach ($messages as $i => $msg) {
            if ($i >= $conversation->message_count) {
                break;
            }
            $timestamp = now()->subMinutes(($conversation->message_count - $i) * 5);

            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $msg[0] === 'admin' ? $admin?->id : null,
                'sender_type' => $msg[0],
                'message_type' => 'text',
                'content' => $msg[1],
                'status' => 'delivered',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    protected function createTeacherClassrooms(array $teachers, array $classrooms): void
    {
        foreach ($teachers as $index => $teacher) {
            if (isset($classrooms[$index])) {
                $exists = DB::table('teacher_classrooms')
                    ->where('teacher_id', (string) $teacher->id)
                    ->where('classroom_id', (string) $classrooms[$index]->id)
                    ->exists();

                if (! $exists) {
                    DB::table('teacher_classrooms')->insert([
                        'teacher_id' => (string) $teacher->id,
                        'classroom_id' => (string) $classrooms[$index]->id,
                    ]);
                }
            }
        }
        $this->command->info('   👨‍🏫 Assigned teachers to classrooms');
    }
}
