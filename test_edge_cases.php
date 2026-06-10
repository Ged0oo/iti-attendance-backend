<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Student;
use App\Models\User;
use App\Models\AttendanceLedger;
use App\Models\ExcuseRequest;
use App\Services\AttendanceLedgerService;
use App\Enums\ExcuseStatus;
use Illuminate\Support\Facades\Auth;

echo "--- Proof of Pass ---\n\n";

// 1. Balance floor at 0
echo "1. Testing balance floor at 0:\n";
// Create a fake student and ledger
$user = User::factory()->create();
$student = Student::create([
    'user_id' => $user->id,
    'cohort_id' => 1, // Assumes cohort 1 exists from previous seeders
    'national_id' => '12345678901234'
]);
$ledger = $student->ledger;
$ledger->update(['balance' => 10]);

$service = new AttendanceLedgerService();
// We'll mock a record just to test deduction
$record = new \App\Models\AttendanceRecord(['id' => 9999]);
$service->deductUnexcused($student, $record);
$ledger->refresh();

echo "Initial balance was 10. After unexcused deduction (-25), balance is: " . $ledger->balance . " (Expected: 0)\n";
if ($ledger->balance === 0) {
    echo "✅ Floor at 0 works.\n";
} else {
    echo "❌ Floor at 0 failed.\n";
}

// 2. Double-review attempt returns 422
echo "\n2. Testing double-review attempt returns 422:\n";
$excuse = ExcuseRequest::create([
    'student_id' => $student->id,
    'attendance_record_id' => 9999,
    'notes' => 'Test reason',
    'status' => ExcuseStatus::Approved
]);

// Attempt to review it again via HTTP request
$request = Illuminate\Http\Request::create('/api/excuse-requests/' . $excuse->id . '/review', 'PATCH', [
    'decision' => 'rejected'
]);
$request->headers->set('Accept', 'application/json');

// Mock auth
\Laravel\Sanctum\Sanctum::actingAs($user, ['*']);

$response = app()->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getContent() . "\n";
if ($response->getStatusCode() == 422 && strpos($response->getContent(), 'Excuse has already been reviewed') !== false) {
    echo "✅ Double-review 422 works.\n";
} else {
    echo "❌ Double-review 422 failed.\n";
}

// 3. Upload .docx file -> 422
echo "\n3. Testing upload .docx file returns 422:\n";

// Create a dummy file
$file = new \Illuminate\Http\UploadedFile(
    __DIR__ . '/phpunit.xml', // use any file
    'test.docx',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    null,
    true
);

$request = Illuminate\Http\Request::create('/api/excuse-requests', 'POST', [
    'attendance_record_id' => 9999,
    'reason' => 'Test reason'
], [], ['attachment' => $file]);
$request->headers->set('Accept', 'application/json');

$response = app()->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getContent() . "\n";
if ($response->getStatusCode() == 422 && strpos($response->getContent(), 'attachment') !== false) {
    echo "✅ .docx upload 422 works.\n";
} else {
    echo "❌ .docx upload 422 failed.\n";
}

// Cleanup
$excuse->delete();
$ledger->entries()->delete();
$ledger->delete();
$student->delete();
$user->delete();

echo "\n--- End Proof of Pass ---\n";
