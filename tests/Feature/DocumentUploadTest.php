<?php

use App\Jobs\AnalyzeSyllabusDocument;
use App\Models\Course;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

it('stores a PDF privately and queues AI analysis after consent', function () {
    Queue::fake();
    Storage::fake('local');

    $organization = Organization::create([
        'name' => 'Test Faculty',
        'slug' => 'test-faculty',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
    ]);

    $user = User::create([
        'organization_id' => $organization->id,
        'name' => 'Instructor',
        'email' => 'teacher@faculty.test',
        'password' => 'StrongPassword123',
    ]);
    $user->assignRole(Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']));

    $course = Course::create([
        'organization_id' => $organization->id,
        'code' => 'CS101',
        'name' => 'Introduction to Computing',
        'credit_hours' => 3,
        'language' => 'en',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post('/documents', [
        'course_id' => $course->id,
        'title' => 'CS101 Syllabus',
        'ai_processing_consent' => '1',
        'document' => UploadedFile::fake()->create('syllabus.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $document = \App\Models\Document::firstOrFail();

    expect($document->organization_id)->toBe($organization->id);
    expect($document->status)->toBe('uploaded');
    Storage::disk('local')->assertExists($document->path);
    Queue::assertPushed(AnalyzeSyllabusDocument::class);
});

it('requires explicit consent before sending a syllabus to AI', function () {
    Queue::fake();

    $organization = Organization::create([
        'name' => 'Consent Faculty',
        'slug' => 'consent-faculty',
        'country' => 'EG',
        'timezone' => 'Africa/Cairo',
    ]);
    $user = User::create([
        'organization_id' => $organization->id,
        'name' => 'Instructor',
        'email' => 'teacher2@faculty.test',
        'password' => 'StrongPassword123',
    ]);
    $user->assignRole(Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']));
    $course = Course::create([
        'organization_id' => $organization->id,
        'code' => 'CS102',
        'name' => 'Data Structures',
        'credit_hours' => 3,
        'language' => 'en',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post('/documents', [
        'course_id' => $course->id,
        'title' => 'CS102 Syllabus',
        'document' => UploadedFile::fake()->create('syllabus.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('ai_processing_consent');

    expect(\App\Models\Document::count())->toBe(0);
    Queue::assertNothingPushed();
});