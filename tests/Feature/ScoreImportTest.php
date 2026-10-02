<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\RegistrationMember;
use App\Models\Score;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ScoreImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_score_template(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $category = Category::create([
            'name' => 'Seni',
            'slug' => 'seni',
        ]);

        $competition = Competition::create([
            'name' => 'Lomba Tahfidz',
            'slug' => 'lomba-tahfidz',
            'code' => 'THF',
            'category_id' => $category->id,
            'type' => 'individual',
            'status' => 'buka',
        ]);

        $registration = Registration::create([
            'user_id' => $admin->id,
            'competition_id' => $competition->id,
            'registration_code' => 'REG-THF-001',
            'participant_number' => '001',
            'institution_name' => 'SDN 1 Blitar',
            'status' => 'verified',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.berita-acara.template-nilai', [
            'competition_id' => $competition->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_import_scores_from_excel(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $category = Category::create([
            'name' => 'Keagamaan',
            'slug' => 'keagamaan',
        ]);

        $competition = Competition::create([
            'name' => 'Lomba MTQ',
            'slug' => 'lomba-mtq',
            'code' => 'MTQ',
            'category_id' => $category->id,
            'type' => 'individual',
            'status' => 'buka',
        ]);

        $reg1 = Registration::create([
            'user_id' => $admin->id,
            'competition_id' => $competition->id,
            'registration_code' => 'REG-MTQ-001',
            'participant_number' => '001',
            'institution_name' => 'MI Al-Huda',
            'status' => 'verified',
        ]);
        RegistrationMember::create([
            'registration_id' => $reg1->id,
            'full_name' => 'Peserta Satu',
            'gender' => 'L',
            'school_name' => 'MI Al-Huda',
        ]);

        $reg2 = Registration::create([
            'user_id' => $admin->id,
            'competition_id' => $competition->id,
            'registration_code' => 'REG-MTQ-002',
            'participant_number' => '002',
            'institution_name' => 'SDN 2 Sananwetan',
            'status' => 'verified',
        ]);
        RegistrationMember::create([
            'registration_id' => $reg2->id,
            'full_name' => 'Peserta Dua',
            'gender' => 'L',
            'school_name' => 'SDN 2 Sananwetan',
        ]);

        // Create mock spreadsheet file
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'ID_REGISTRASI');
        $sheet->setCellValue('C5', 'NO_PESERTA');
        $sheet->setCellValue('D5', 'NAMA_PESERTA');
        $sheet->setCellValue('E5', 'ASAL_SEKOLAH');
        $sheet->setCellValue('F5', 'SEKTOR_KATEGORI');
        $sheet->setCellValue('G5', 'NILAI_TOTAL');
        $sheet->setCellValue('H5', 'CATATAN_JURI');

        // Row 6: reg1 score 95.5
        $sheet->setCellValue('A6', 1);
        $sheet->setCellValue('B6', 'REG-'.$reg1->id);
        $sheet->setCellValue('C6', '001');
        $sheet->setCellValue('D6', 'Peserta Satu');
        $sheet->setCellValue('E6', 'MI Al-Huda');
        $sheet->setCellValue('F6', 'Putra (PA)');
        $sheet->setCellValue('G6', 95.5);
        $sheet->setCellValue('H6', 'Sangat baik');

        // Row 7: reg2 score 88.0
        $sheet->setCellValue('A7', 2);
        $sheet->setCellValue('B7', 'REG-'.$reg2->id);
        $sheet->setCellValue('C7', '002');
        $sheet->setCellValue('D7', 'Peserta Dua');
        $sheet->setCellValue('E7', 'SDN 2 Sananwetan');
        $sheet->setCellValue('F7', 'Putra (PA)');
        $sheet->setCellValue('G7', 88.0);
        $sheet->setCellValue('H7', 'Cukup baik');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_score_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile(
            $tempPath,
            'test_score.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($admin)->post(route('admin.berita-acara.import-nilai'), [
            'competition_id' => $competition->id,
            'excel_file' => $file,
            'lock_scores' => '1',
        ]);

        $response->assertRedirect(route('admin.berita-acara.index', ['competition_id' => $competition->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('scores', [
            'competition_id' => $competition->id,
            'registration_id' => $reg1->id,
            'total_score' => 95.5,
            'is_locked' => true,
        ]);

        $this->assertDatabaseHas('scores', [
            'competition_id' => $competition->id,
            'registration_id' => $reg2->id,
            'total_score' => 88.0,
            'is_locked' => true,
        ]);

        // Verify Certificate Juara page immediately shows the winners
        $certResponse = $this->actingAs($admin)->get(route('admin.certificates.index', [
            'competition_id' => $competition->id,
            'type' => 'juara',
        ]));
        $certResponse->assertStatus(200);
        $certResponse->assertSee('Juara 1');
        $certResponse->assertSee('MI Al-Huda');
        $certResponse->assertSee('95.5');
        $certResponse->assertSee('Juara 2');
        $certResponse->assertSee('SDN 2 Sananwetan');
        $certResponse->assertSee('88');

        // Verify Certificate Bulk Print renders the winners with their respective ranks
        $printResponse = $this->actingAs($admin)->get(route('admin.certificates.print.bulk', [
            'competition_id' => $competition->id,
            'type' => 'juara',
        ]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Juara 1');
        $printResponse->assertSee('Juara 2');

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}
