<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class ContactController extends Controller
{
    /**
     * @return 'math'|'recaptcha'|'none'
     */
    protected function contactCaptchaDriver(): string
    {
        $d = strtolower((string) config('services.contact_captcha.driver', 'math'));

        return in_array($d, ['math', 'recaptcha', 'none'], true) ? $d : 'math';
    }

    /**
     * Always send validation errors back to the landing page so the contact modal can reopen with @errors (see open-on-load).
     * Relying on url()->previous() often leaves users on a URL where the modal is closed — failures feel “silent”.
     */
    protected function contactFormRedirectUrl(): string
    {
        return route('landing', absolute: false);
    }

    /**
     * Never flash g-recaptcha-response into old input: tokens are single-use. Re-posting a “stale” token breaks the next submit
     * (Google: timeout-or-duplicate / invalid-input-response) — often after several attempts.
     * Do not flash contact_captcha_answer so users re-enter after a wrong math answer.
     */
    protected function contactFormValidationRedirect(Request $request, array|\Illuminate\Contracts\Validation\Validator $errors): RedirectResponse
    {
        return redirect()
            ->to($this->contactFormRedirectUrl())
            ->withFragment('contact')
            ->withErrors($errors)
            ->withInput($request->except(['g-recaptcha-response', 'contact_captcha_answer']));
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = [
            'name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'message' => 'message',
            'g-recaptcha-response' => 'captcha verification',
            'contact_captcha_answer' => 'security question',
        ];

        $base = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:65000'],
        ], [], $attributes);

        if ($base->fails()) {
            return $this->contactFormValidationRedirect($request, $base);
        }

        $validated = $base->validated();

        $cleanMessage = Purify::config('notification_faq')->clean($validated['message']);
        if (trim(strip_tags($cleanMessage)) === '') {
            return $this->contactFormValidationRedirect($request, [
                'message' => __('Please enter a message.'),
            ]);
        }

        $driver = $this->contactCaptchaDriver();

        if ($driver === 'math') {
            $a = (int) session('contact_math_a', 0);
            $b = (int) session('contact_math_b', 0);
            if ($a < 1 || $b < 1 || $a > 99 || $b > 99) {
                return $this->contactFormValidationRedirect($request, [
                    'contact_captcha_answer' => __('This page was open too long. Please refresh the page and try again.'),
                ]);
            }

            $math = Validator::make($request->all(), [
                'contact_captcha_answer' => ['required', 'integer'],
            ], [], $attributes);

            if ($math->fails()) {
                return $this->contactFormValidationRedirect($request, $math);
            }

            $expected = $a + $b;
            $given = (int) $request->input('contact_captcha_answer');
            if ($given !== $expected) {
                session([
                    'contact_math_a' => random_int(1, 9),
                    'contact_math_b' => random_int(1, 9),
                ]);

                return $this->contactFormValidationRedirect($request, [
                    'contact_captcha_answer' => __('Incorrect answer. Please solve the new question below.'),
                ]);
            }
        } elseif ($driver === 'recaptcha') {
            if (! filled(config('services.recaptcha.secret'))) {
                Log::error('Contact form: recaptcha driver selected but RECAPTCHA_SECRET_KEY is empty');

                return $this->contactFormValidationRedirect($request, [
                    'contact_form' => __('The contact form could not verify your request. Please try again later.'),
                ]);
            }

            $captcha = Validator::make($request->all(), [
                'g-recaptcha-response' => ['required', 'string'],
            ], [], $attributes);

            if ($captcha->fails()) {
                return $this->contactFormValidationRedirect($request, $captcha);
            }

            if (! $this->verifyRecaptcha((string) $request->input('g-recaptcha-response'))) {
                return $this->contactFormValidationRedirect($request, [
                    'g-recaptcha-response' => __('Captcha verification failed. Please tick “I’m not a robot” again.'),
                ]);
            }
        }

        try {
            $stored = ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $cleanMessage,
            ]);
        } catch (QueryException $e) {
            Log::error('Contact message could not be saved to the database', [
                'message' => $e->getMessage(),
            ]);

            return $this->contactFormValidationRedirect($request, [
                'contact_form' => __('We could not save your message. Please try again, or email us directly if this keeps happening.'),
            ]);
        }

        Log::info('Contact message stored', [
            'contact_message_id' => $stored->id,
            'email' => $stored->email,
        ]);

        session()->forget(['contact_math_a', 'contact_math_b']);

        return redirect()
            ->to(route('landing', absolute: false))
            ->withFragment('contact')
            ->with(
                'contact_success_toast',
                __('Thank you! Your message has been sent. We will get back to you soon.')
            );
    }

    protected function verifyRecaptcha(string $token): bool
    {
        $secret = config('services.recaptcha.secret');
        if (! is_string($secret) || $secret === '') {
            return true;
        }

        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ]);

            $success = $response->json('success') === true;
            if (! $success) {
                Log::warning('reCAPTCHA siteverify failed', [
                    'error-codes' => $response->json('error-codes'),
                    'status' => $response->status(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA siteverify exception', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
