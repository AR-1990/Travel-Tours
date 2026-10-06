<?php

namespace Tests\Feature;

use App\Models\Content\PartnerInquiry;
use Tests\TestCase;

class PartnerInquiryTest extends TestCase
{
    public function test_partner_form_persists_an_inquiry(): void
    {
        $email = 'partner-test-'.uniqid('', true).'@example.com';

        $response = $this->from(route('pages.partner-with-us'))
            ->post(route('pages.partner-with-us.submit'), [
                'partner_type' => 'b2b',
                'name' => 'Test Agency Owner',
                'email' => $email,
                'phone' => '+92 300 0000000',
                'company' => 'Test Travel Co',
                'message' => 'We want B2B portal access.',
            ]);

        $response->assertRedirect(route('pages.partner-with-us'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('partner_inquiries', [
            'email' => $email,
            'partner_type' => 'b2b',
            'name' => 'Test Agency Owner',
            'status' => PartnerInquiry::STATUS_NEW,
        ]);

        PartnerInquiry::query()->where('email', $email)->delete();
    }

    public function test_partner_form_requires_core_fields(): void
    {
        $response = $this->from(route('pages.partner-with-us'))
            ->post(route('pages.partner-with-us.submit'), []);

        $response->assertRedirect(route('pages.partner-with-us'));
        $response->assertSessionHasErrors(['partner_type', 'name', 'email', 'message']);
    }
}
