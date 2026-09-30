<?php

namespace Tests\Feature;

use App\Models\CertificateTemplate;
use App\Models\User;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    public function test_public_certificate_verify_page_is_accessible(): void
    {
        $response = $this->get('/verifikasi-sertifikat/TLT-JRA-MTQ-0001');
        $response->assertStatus(200);
        $response->assertSee('Verifikasi');
    }

    public function test_admin_can_access_certificate_index_and_designer(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.index'));
        $response->assertStatus(200);
        $response->assertSee('Sertifikat & Piagam');

        $response = $this->actingAs($admin)->get(route('admin.certificates.designer', ['type' => 'juara']));
        $response->assertStatus(200);
        $response->assertSee('Desainer Template');
    }

    public function test_admin_can_save_certificate_layout(): void
    {
        $this->withoutMiddleware();

        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $template = CertificateTemplate::create([
            'name' => 'Template Test',
            'type' => 'juara',
            'layout_config' => CertificateTemplate::defaultLayoutConfig(),
        ]);

        $newLayout = CertificateTemplate::defaultLayoutConfig();
        $newLayout['nama']['size'] = 40;

        $response = $this->actingAs($admin)->postJson(route('admin.certificates.template.layout', $template->id), [
            'layout_config' => $newLayout,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $template->refresh();
        $this->assertEquals(40, $template->layout_config['nama']['size']);
    }

    public function test_peserta_certificate_predikat_defaults_to_peserta(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.print', ['type' => 'peserta']));
        $response->assertStatus(200);
        $response->assertSee('PESERTA');
    }

    public function test_admin_can_save_custom_predikat_text(): void
    {
        $this->withoutMiddleware();

        $admin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $template = CertificateTemplate::create([
            'name' => 'Template Peserta Test',
            'type' => 'peserta',
            'layout_config' => CertificateTemplate::defaultLayoutConfig(),
        ]);

        $newLayout = CertificateTemplate::defaultLayoutConfig();
        $newLayout['predikat']['text'] = 'PESERTA TELADAN';

        $response = $this->actingAs($admin)->postJson(route('admin.certificates.template.layout', $template->id), [
            'layout_config' => $newLayout,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $template->refresh();
        $this->assertEquals('PESERTA TELADAN', $template->layout_config['predikat']['text']);
    }
}
