<?php

use App\Models\ContactMessage;

it('stores a contact message when posting to /contact', function () {
    config([
        'services.contact_captcha.driver' => 'math',
        'services.recaptcha.secret' => null,
        'services.recaptcha.site_key' => null,
    ]);

    $response = $this->withSession([
        'contact_math_a' => 2,
        'contact_math_b' => 3,
    ])->from('/')
        ->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1 234 567 8901',
            'message' => '<p>Hello from automated test.</p>',
            'contact_captcha_answer' => 5,
            '_contact_form_source' => 'modal',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('contact_success_toast');

    expect(ContactMessage::query()->where('email', 'test@example.com')->exists())->toBeTrue();
});
