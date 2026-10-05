<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\EventEdition;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiYearEditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_edition_is_2026(): void
    {
        $year = AppSetting::getActiveYear();
        $this->assertEquals('2026', $year);

        $edition = EventEdition::getActiveEdition();
        $this->assertNotNull($edition);
        $this->assertEquals('2026', $edition->year);
    }

    public function test_registration_and_invoice_are_partitioned_by_event_year(): void
    {
        $user = User::factory()->create(['role' => 'peserta']);
        $category = Category::create(['name' => 'Umum', 'slug' => 'umum', 'target_level' => 'semua']);
        $competition = Competition::create([
            'category_id' => $category->id,
            'name' => 'Tahfidz',
            'slug' => 'tahfidz',
            'code' => 'TFZ',
            'max_quota' => 50,
            'status' => 'buka',
            'registration_fee' => 0,
        ]);

        // 1. Create registration & invoice in 2026
        $reg2026 = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'registration_code' => 'TFZ-2026-001',
            'institution_name' => 'SD Negeri 1 Blitar',
            'status' => 'verified',
        ]);

        $invoice2026 = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => 'INV-2026-001',
            'total_amount' => 100000,
            'unique_code' => 123,
            'final_amount' => 100123,
            'status' => 'verified',
        ]);

        $this->assertEquals('2026', $reg2026->event_year);
        $this->assertEquals('2026', $invoice2026->event_year);
        $this->assertEquals(1, Registration::count());
        $this->assertEquals(1, Invoice::count());

        // 2. Switch to 2027
        EventEdition::activateEdition('2027');
        $this->assertEquals('2027', AppSetting::getActiveYear());

        // Under 2027, previous data must appear clean / empty
        $this->assertEquals(0, Registration::count());
        $this->assertEquals(0, Invoice::count());
        $this->assertEquals(0, $competition->registrations()->count());

        // 3. Create new registration in 2027
        $reg2027 = Registration::create([
            'competition_id' => $competition->id,
            'user_id' => $user->id,
            'registration_code' => 'TFZ-2027-001',
            'institution_name' => 'SD Negeri 2 Blitar',
            'status' => 'verified',
        ]);

        $this->assertEquals('2027', $reg2027->event_year);
        $this->assertEquals(1, Registration::count());
        $this->assertEquals(1, $competition->registrations()->count());

        // 4. Switch back to 2026
        EventEdition::activateEdition('2026');
        $this->assertEquals('2026', AppSetting::getActiveYear());

        // Under 2026, 2026 records are restored and 2027 records are isolated
        $this->assertEquals(1, Registration::count());
        $this->assertEquals('TFZ-2026-001', Registration::first()->registration_code);
        $this->assertEquals(1, Invoice::count());

        // 5. Total records across all years can still be queried
        $this->assertEquals(2, Registration::allYears()->count());
        $this->assertEquals(1, Invoice::allYears()->count());
    }

    public function test_admin_can_create_and_switch_edition_via_http(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        // Create new edition 2029 (only passing year, should auto-derive Milad ke-60)
        $response = $this->actingAs($admin)->post(route('admin.settings.editions.store'), [
            'year' => '2029',
            'activate_now' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.general', ['tab' => 'identitas']));
        $this->assertEquals('2029', AppSetting::getActiveYear());
        $this->assertEquals('Milad ke-60 MTsN 1 Blitar', AppSetting::getActiveEventName());

        // Switch back to 2026
        $responseSwitch = $this->actingAs($admin)->post(route('admin.editions.switch', '2026'));
        $this->assertEquals('2026', AppSetting::getActiveYear());

        // Access General Settings page
        $responseGet = $this->actingAs($admin)->get(route('admin.settings.general'));
        $responseGet->assertOk();
        $responseGet->assertSee('Identitas Instansi & Headings Surat Resmi', false);
        $responseGet->assertSee('Tahun Kegiatan');
    }
}
