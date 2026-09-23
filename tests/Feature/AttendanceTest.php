<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    protected function createSampleData()
    {
        $unique = uniqid();

        $category = Category::firstOrCreate(['slug' => 'seni-islam'], ['name' => 'Seni Islam']);

        $pic = User::factory()->create([
            'role' => 'pic_lomba',
            'name' => 'PIC Lomba MTQ '.$unique,
        ]);

        $competition = Competition::firstOrCreate(
            ['code' => 'MTQ_'.$unique],
            [
                'name' => 'Musabaqah Tilawatil Qur\'an '.$unique,
                'slug' => 'mtq-'.$unique,
                'category_id' => $category->id,
                'user_id' => $pic->id,
                'is_active' => true,
            ]
        );

        $pesertaUser = User::factory()->create([
            'role' => 'peserta',
            'name' => 'Ahmad Peserta '.$unique,
            'institution_name' => 'MI Al-Falah',
        ]);

        $registration = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $pesertaUser->id,
            'registration_code' => 'TLT-TEST-'.$unique,
            'participant_number' => 'MTQ-'.$unique,
            'institution_name' => 'MI Al-Falah',
            'status' => 'verified',
            'is_attended' => false,
        ]);

        RegistrationMember::create([
            'registration_id' => $registration->id,
            'full_name' => 'Ahmad Peserta',
            'nisn' => '1234567890',
            'gender' => 'L',
        ]);

        return compact('pic', 'competition', 'pesertaUser', 'registration');
    }

    public function test_admin_and_pic_can_access_attendance_page(): void
    {
        $data = $this->createSampleData();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $responseAdmin = $this->actingAs($admin)->get(route('admin.attendance.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Daftar Hadir');

        $responsePic = $this->actingAs($data['pic'])->get(route('admin.attendance.index'));
        $responsePic->assertStatus(200);
        $responsePic->assertSee('Daftar Hadir');
    }

    public function test_scanning_qr_code_marks_attendance_and_sets_timestamp(): void
    {
        $data = $this->createSampleData();
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($admin)->postJson(route('admin.attendance.scan'), [
            'code' => $data['registration']->registration_code,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_attended' => false,
        ]);

        $data['registration']->refresh();
        $this->assertTrue((bool) $data['registration']->is_attended);
        $this->assertNotNull($data['registration']->attended_at);
        $this->assertEquals($admin->id, $data['registration']->attended_by);

        // Scan second time should report already attended
        $secondResponse = $this->actingAs($admin)->postJson(route('admin.attendance.scan'), [
            'code' => $data['registration']->registration_code,
        ]);

        $secondResponse->assertStatus(200);
        $secondResponse->assertJson([
            'success' => true,
            'already_attended' => true,
        ]);
    }

    public function test_certificate_is_gated_by_attendance_status(): void
    {
        $data = $this->createSampleData();
        $registration = $data['registration'];
        $peserta = $data['pesertaUser'];

        // Release certificate for this competition
        AppSetting::updateOrCreate(
            ['key' => 'certificate_release_'.$registration->competition_id],
            ['value' => '1']
        );

        // 1. Participant is NOT attended yet: Certificate download must be blocked
        $responseBeforeAttendance = $this->actingAs($peserta)->get(route('peserta.certificate.download', $registration->id));
        $responseBeforeAttendance->assertRedirect(route('peserta.registration.detail', $registration->id));
        $responseBeforeAttendance->assertSessionHas('error');

        // 2. Mark participant as attended
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->postJson(route('admin.attendance.scan'), [
            'code' => $registration->registration_code,
        ]);

        $registration->refresh();
        $this->assertTrue((bool) $registration->is_attended);

        // 3. Participant IS attended: Certificate download must succeed (200 OK)
        $responseAfterAttendance = $this->actingAs($peserta)->get(route('peserta.certificate.download', $registration->id));
        $responseAfterAttendance->assertStatus(200);
    }

    public function test_manual_toggle_attendance_status(): void
    {
        $data = $this->createSampleData();
        $admin = User::factory()->create(['role' => 'superadmin']);
        $registration = $data['registration'];

        $this->assertFalse((bool) $registration->is_attended);

        // Toggle to Hadir
        $this->actingAs($admin)->post(route('admin.attendance.toggle', $registration->id));
        $registration->refresh();
        $this->assertTrue((bool) $registration->is_attended);

        // Toggle to Belum Hadir
        $this->actingAs($admin)->post(route('admin.attendance.toggle', $registration->id));
        $registration->refresh();
        $this->assertFalse((bool) $registration->is_attended);
    }
}
