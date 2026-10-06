<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_photo_for_competition_members(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'peserta']);
        $category = Category::create(['name' => 'Umum', 'slug' => 'umum', 'target_level' => 'semua']);
        $competition = Competition::create([
            'category_id' => $category->id,
            'name' => 'Tahfidz Al-Quran',
            'slug' => 'tahfidz',
            'code' => 'TFZ',
            'max_quota' => 50,
            'status' => 'buka',
            'registration_fee' => 0,
        ]);

        // Attempt submit without member photo
        $response = $this->actingAs($user)->post(route('peserta.register.competition.store', $competition->slug), [
            'institution_name' => 'SD Negeri 1 Blitar',
            'members' => [
                [
                    'full_name' => 'Ahmad Santoso',
                    'gender' => 'L',
                    'nisn' => '1234567890',
                ],
            ],
            'document_file' => UploadedFile::fake()->create('surat.pdf', 100),
            'payment_proof' => UploadedFile::fake()->create('bukti.jpg', 100),
        ]);

        $response->assertSessionHasErrors(['members.0.photo']);

        // Submit WITH member photo
        $responseSuccess = $this->actingAs($user)->post(route('peserta.register.competition.store', $competition->slug), [
            'institution_name' => 'SD Negeri 1 Blitar',
            'members' => [
                [
                    'full_name' => 'Ahmad Santoso',
                    'gender' => 'L',
                    'nisn' => '1234567890',
                    'photo' => UploadedFile::fake()->image('pasfoto.jpg', 300, 400),
                ],
            ],
            'document_file' => UploadedFile::fake()->create('surat.pdf', 100),
            'payment_proof' => UploadedFile::fake()->create('bukti.jpg', 100),
        ]);

        $member = RegistrationMember::first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->photo);
        $responseSuccess->assertRedirect(route('peserta.registration.detail', $member->registration_id));
    }

    public function test_peserta_can_upload_photo_for_existing_member(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'peserta']);
        $category = Category::create(['name' => 'Umum', 'slug' => 'umum', 'target_level' => 'semua']);
        $competition = Competition::create([
            'category_id' => $category->id,
            'name' => 'Catur Cepat',
            'slug' => 'catur',
            'code' => 'CTR',
            'max_quota' => 50,
            'status' => 'buka',
            'registration_fee' => 0,
        ]);

        $reg = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'institution_name' => 'SD Negeri 2 Blitar',
            'status' => 'verified',
            'registration_code' => 'CTR-001',
        ]);

        $member = RegistrationMember::create([
            'registration_id' => $reg->id,
            'full_name' => 'Budi Pratama',
            'gender' => 'L',
            'nisn' => '9876543210',
            'photo' => null, // initially null
        ]);

        $this->assertNull($member->photo);

        $response = $this->actingAs($user)->post(
            route('peserta.registration.upload_photo', [$reg->id, $member->id]),
            [
                'photo' => UploadedFile::fake()->image('foto_budi.jpg', 300, 400),
            ]
        );

        $response->assertSessionHas('success');
        $member->refresh();
        $this->assertNotNull($member->photo);
    }

    public function test_admin_can_upload_photo_for_member(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'superadmin']);
        $peserta = User::factory()->create(['role' => 'peserta']);
        $category = Category::create(['name' => 'Umum', 'slug' => 'umum', 'target_level' => 'semua']);
        $competition = Competition::create([
            'category_id' => $category->id,
            'name' => 'Matematika',
            'slug' => 'matematika',
            'code' => 'MTK',
            'max_quota' => 50,
            'status' => 'buka',
            'registration_fee' => 0,
        ]);

        $reg = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $peserta->id,
            'institution_name' => 'MI Al Huda',
            'status' => 'verified',
            'registration_code' => 'MTK-001',
        ]);

        $member = RegistrationMember::create([
            'registration_id' => $reg->id,
            'full_name' => 'Siti Nurhaliza',
            'gender' => 'P',
            'nisn' => '5556667770',
            'photo' => null,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.participants.upload_photo', [$reg->id, $member->id]),
            [
                'photo' => UploadedFile::fake()->image('foto_siti.jpg', 300, 400),
            ]
        );

        $response->assertSessionHas('success');
        $member->refresh();
        $this->assertNotNull($member->photo);
    }
}
