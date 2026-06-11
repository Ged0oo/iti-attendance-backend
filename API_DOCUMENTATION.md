# API Discovery & Documentation

| Method | URI | Middleware | Action |
|---|---|---|---|
| POST | `api/login` | api, guest | App\Http\Controllers\Auth\AuthController@store |
| POST | `api/logout` | api, auth:sanctum | App\Http\Controllers\Auth\AuthController@destroy |
| GET | `api/me` | api, auth:sanctum, check.expiry | Closure |
| GET | `api/cohorts/{cohort}/students` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\StudentController@index |
| POST | `api/students` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\StudentController@store |
| GET | `api/students/at-risk` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\StudentController@atRisk |
| PUT | `api/students/{student}` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\StudentController@update |
| PATCH | `api/students/{student}/lab-group` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\StudentController@assignLabGroup |
| GET | `api/students/{student}` | api, auth:sanctum, check.expiry | App\Http\Controllers\StudentController@show |
| GET | `api/students/{student}/ledger` | api, auth:sanctum, check.expiry | App\Http\Controllers\AttendanceLedgerController@show |
| GET | `api/students/{student}/ledger/entries` | api, auth:sanctum, check.expiry | App\Http\Controllers\AttendanceLedgerController@entries |
| GET | `api/excuse-requests` | api, auth:sanctum, check.expiry | App\Http\Controllers\ExcuseRequestController@index |
| GET | `api/excuse-requests/{excuse}` | api, auth:sanctum, check.expiry | App\Http\Controllers\ExcuseRequestController@show |
| POST | `api/excuse-requests` | api, auth:sanctum, check.expiry, role:student | App\Http\Controllers\ExcuseRequestController@store |
| PATCH | `api/excuse-requests/{excuse}/review` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\ExcuseRequestController@review |
| GET | `api/users` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\UserController@index |
| POST | `api/users` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\UserController@store |
| PATCH | `api/users/{user}` | api, auth:sanctum, check.expiry, role:track_admin,branch_manager | App\Http\Controllers\UserController@update |
| POST | `api/attendance/scan` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\AttendanceController@scan |
| GET | `api/sessions/{session}/attendance` | api, auth:sanctum, check.expiry, role:instructor,track_admin,branch_manager | App\Http\Controllers\Api\SessionAttendanceController@index |
| POST | `api/sessions/{session}/close` | api, auth:sanctum, check.expiry, role:instructor,track_admin,branch_manager | App\Http\Controllers\Api\SessionAttendanceController@close |
| GET | `api/sessions/{session}/qr-code` | api, auth:sanctum, check.expiry, role:instructor,track_admin,branch_manager | App\Http\Controllers\Api\QrCodeController@generate |
| POST | `api/nfc/register` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\NfcAttendanceController@register |
| POST | `api/nfc/lost` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\NfcAttendanceController@reportLost |
| POST | `api/nfc/scan` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\NfcAttendanceController@scan |
| GET | `api/courses` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\CourseController@index |
| GET | `api/courses/{course}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\CourseController@show |
| GET | `api/grade-components` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\GradeComponentController@index |
| GET | `api/grade-components/{grade_component}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\GradeComponentController@show |
| GET | `api/instructors` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\InstructorController@index |
| GET | `api/instructors/{instructor}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\InstructorController@show |
| GET | `api/lab-groups` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\LabGroupController@index |
| GET | `api/lab-groups/{lab_group}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\LabGroupController@show |
| GET | `api/engagements` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\EngagementController@index |
| GET | `api/engagements/{engagement}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\EngagementController@show |
| GET | `api/sessions` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\SessionController@index |
| GET | `api/sessions/{session}` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\SessionController@show |
| GET | `api/cohorts/{cohort}/courses` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\CourseController@index |
| GET | `api/courses/{course}/components` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\GradeComponentController@index |
| GET | `api/cohorts/{cohort}/lab-groups` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\LabGroupController@index |
| GET | `api/cohorts/{cohort}/engagements` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\EngagementController@index |
| GET | `api/engagements/{engagement}/sessions` | api, auth:sanctum, check.expiry | App\Http\Controllers\Api\SessionController@index |
| POST | `api/courses` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\CourseController@store |
| PUT|PATCH | `api/courses/{course}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\CourseController@update |
| DELETE | `api/courses/{course}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\CourseController@destroy |
| POST | `api/grade-components` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\GradeComponentController@store |
| PUT|PATCH | `api/grade-components/{grade_component}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\GradeComponentController@update |
| DELETE | `api/grade-components/{grade_component}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\GradeComponentController@destroy |
| POST | `api/instructors` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\InstructorController@store |
| PUT|PATCH | `api/instructors/{instructor}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\InstructorController@update |
| DELETE | `api/instructors/{instructor}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\InstructorController@destroy |
| POST | `api/lab-groups` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\LabGroupController@store |
| PUT|PATCH | `api/lab-groups/{lab_group}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\LabGroupController@update |
| DELETE | `api/lab-groups/{lab_group}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\LabGroupController@destroy |
| POST | `api/engagements` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\EngagementController@store |
| PUT|PATCH | `api/engagements/{engagement}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\EngagementController@update |
| DELETE | `api/engagements/{engagement}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\EngagementController@destroy |
| POST | `api/sessions` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\SessionController@store |
| PUT|PATCH | `api/sessions/{session}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\SessionController@update |
| DELETE | `api/sessions/{session}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\SessionController@destroy |
| POST | `api/engagements/{engagement}/sessions/generate` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\SessionController@generate |
| PATCH | `api/sessions/{session}/deliver` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\SessionController@deliver |
| GET | `api/billing` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:branch_manager | App\Http\Controllers\Api\BillingController@index |
| POST | `api/billing/calculate` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:branch_manager | App\Http\Controllers\Api\BillingController@calculate |
| GET | `api/billing/{billingRecord}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:branch_manager | App\Http\Controllers\Api\BillingController@show |
| PATCH | `api/billing/{billingRecord}/finalize` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:branch_manager | App\Http\Controllers\Api\BillingController@finalize |
| GET | `api/grade-distribution` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\GradeDistributionController |
| GET | `api/grades` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\GradeController@index |
| POST | `api/grades` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\GradeController@store |
| GET | `api/grades/{grade}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\GradeController@show |
| PUT|PATCH | `api/grades/{grade}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\GradeController@update |
| GET | `api/assignment-submissions` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\AssignmentSubmissionController@index |
| GET | `api/assignment-submissions/{assignmentSubmission}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\AssignmentSubmissionController@show |
| GET | `api/student-tags` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentTagController@index |
| POST | `api/student-tags` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentTagController@store |
| GET | `api/student-tags/{studentTag}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentTagController@show |
| PUT|PATCH | `api/student-tags/{studentTag}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentTagController@update |
| GET | `api/student-notes` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentNoteController@index |
| POST | `api/student-notes` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentNoteController@store |
| GET | `api/student-notes/{studentNote}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentNoteController@show |
| PUT|PATCH | `api/student-notes/{studentNote}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentNoteController@update |
| POST | `api/assignment-submissions` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:student|track_admin|branch_manager | App\Http\Controllers\Api\AssignmentSubmissionController@store |
| GET | `api/students/{student}/grade-card` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:student|instructor|track_admin|branch_manager | App\Http\Controllers\Api\StudentGradeCardController |
| PATCH | `api/grades/{grade}/override` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\GradeOverrideController |
| DELETE | `api/grades/{grade}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\GradeController@destroy |
| DELETE | `api/assignment-submissions/{assignmentSubmission}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\AssignmentSubmissionController@destroy |
| DELETE | `api/student-tags/{studentTag}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\StudentTagController@destroy |
| DELETE | `api/student-notes/{studentNote}` | api, auth:sanctum, check.expiry, Spatie\Permission\Middleware\RoleMiddleware:track_admin|branch_manager | App\Http\Controllers\Api\StudentNoteController@destroy |
| GET | `api/tracks` | api, auth:sanctum, check.expiry | App\Http\Controllers\TrackController@index |
| GET | `api/tracks/{track}` | api, auth:sanctum, check.expiry | App\Http\Controllers\TrackController@show |
| POST | `api/tracks` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\TrackController@store |
| PUT | `api/tracks/{track}` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\TrackController@update |
| DELETE | `api/tracks/{track}` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\TrackController@destroy |
| GET | `api/tracks/{track}/admins` | api, auth:sanctum, check.expiry | App\Http\Controllers\TrackAdminController@index |
| POST | `api/tracks/{track}/admins` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\TrackAdminController@store |
| DELETE | `api/tracks/{track}/admins/{userId}` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\TrackAdminController@destroy |
| GET | `api/cohorts` | api, auth:sanctum, check.expiry | App\Http\Controllers\CohortController@index |
| GET | `api/cohorts/{cohort}` | api, auth:sanctum, check.expiry | App\Http\Controllers\CohortController@show |
| POST | `api/cohorts` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\CohortController@store |
| PUT | `api/cohorts/{cohort}` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\CohortController@update |
| DELETE | `api/cohorts/{cohort}` | api, auth:sanctum, check.expiry, role:branch_manager | App\Http\Controllers\CohortController@destroy |
| PATCH | `api/cohorts/{cohort}/transition` | api, auth:sanctum, check.expiry, role:branch_manager,track_admin | App\Http\Controllers\CohortController@transition |
| GET | `api/cohorts/{cohort}/announcements` | api, auth:sanctum, check.expiry | App\Http\Controllers\AnnouncementController@index |
| GET | `api/announcements/{announcement}` | api, auth:sanctum, check.expiry | App\Http\Controllers\AnnouncementController@show |
| POST | `api/announcements` | api, auth:sanctum, check.expiry, role:track_admin,instructor | App\Http\Controllers\AnnouncementController@store |
| PUT | `api/announcements/{announcement}` | api, auth:sanctum, check.expiry, role:track_admin,instructor | App\Http\Controllers\AnnouncementController@update |
| DELETE | `api/announcements/{announcement}` | api, auth:sanctum, check.expiry, role:track_admin,instructor | App\Http\Controllers\AnnouncementController@destroy |
