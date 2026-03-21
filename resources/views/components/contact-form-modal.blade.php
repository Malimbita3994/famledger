{{--
  Reusable contact modal: name, email, phone, rich message (Quill), spam protection (math question by default or optional reCAPTCHA v2).
  POSTs to /contact by default (same-origin relative URL so 127.0.0.1 vs localhost matches APP_URL).

  Requirements on the page:
  - Bootstrap 3 + jQuery (before this modal’s scripts)
  - Quill 1.3.x + quill.snow.css (landing uses public/vendor/quill for same-origin / strict browsers)
  - Optional: window.__recaptchaSiteKey + google recaptcha api.js when CONTACT_CAPTCHA_DRIVER=recaptcha

  Usage:
    <x-contact-form-modal />
    <x-contact-form-modal modal-id="helpContactModal" :open-on-load="$errors->any() && old('_contact_form_source') === 'help'" />

  Open from a button:
    <button type="button" data-toggle="modal" data-target="#landingContactModal">Contact</button>

  @see public/css/famledger-contact-form-modal.css
  @see public/js/famledger-contact-form-modal.js
--}}
@props([
    'modalId' => 'landingContactModal',
    'title' => null,
    'action' => null,
    'formId' => null,
    'formSource' => 'modal',
    'submitButtonClass' => 'section-btn hero-cta',
    'closeButtonClass' => 'btn btn-default',
    'captchaDriver' => null,
    'recaptchaSiteKey' => null,
    'openOnLoad' => false,
])

@php
    $titleText = $title ?? __('Talk to the FamLedger team');
    $actionUrl = $action ?? route('contact.store', absolute: false);
    $captchaDriverResolved = strtolower((string) ($captchaDriver ?? config('services.contact_captcha.driver', 'math')));
    if (! in_array($captchaDriverResolved, ['math', 'recaptcha', 'none'], true)) {
        $captchaDriverResolved = 'math';
    }
    $useRecaptcha = $captchaDriverResolved === 'recaptcha';
    $useMath = $captchaDriverResolved === 'math';
    $siteKey = $useRecaptcha ? ($recaptchaSiteKey ?? config('services.recaptcha.site_key')) : null;
    if ($useMath && ! session()->has('contact_math_a')) {
        session([
            'contact_math_a' => random_int(1, 9),
            'contact_math_b' => random_int(1, 9),
        ]);
    }
    $mathA = $useMath ? (int) session('contact_math_a') : 0;
    $mathB = $useMath ? (int) session('contact_math_b') : 0;
    $resolvedFormId = $formId ?? ($modalId === 'landingContactModal'
        ? 'landing-contact-form'
        : 'famledger-contact-form-'.\Illuminate\Support\Str::slug($modalId));
    $titleId = $modalId.'Title';
    $nameId = $modalId.'_contact_name';
    $emailId = $modalId.'_contact_email';
    $phoneId = $modalId.'_contact_phone';
    $quillId = $modalId.'_quill';
    $hiddenMessageId = $modalId.'_message_hidden';
    $recaptchaId = $modalId.'_recaptcha';
    $mathAnswerId = $modalId.'_contact_math_answer';
    $lblQuillId = $modalId.'_lbl_quill';
    $openFlag = filter_var($openOnLoad, FILTER_VALIDATE_BOOLEAN);
@endphp

<div
    class="modal fade famledger-contact-form-modal"
    id="{{ $modalId }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $titleId }}"
    data-form-id="{{ $resolvedFormId }}"
    data-quill-container-id="{{ $quillId }}"
    data-hidden-message-id="{{ $hiddenMessageId }}"
    data-recaptcha-container-id="{{ filled($siteKey) ? $recaptchaId : '' }}"
    data-captcha-driver="{{ $captchaDriverResolved }}"
    data-open-on-load="{{ $openFlag ? '1' : '0' }}"
>
    <div class="modal-dialog landing-contact-modal-wide" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="{{ $titleId }}">{{ $titleText }}</h4>
            </div>
            <form method="post" action="{{ $actionUrl }}" id="{{ $resolvedFormId }}" class="famledger-contact-modal-form" novalidate>
                @csrf
                <input type="hidden" name="_contact_form_source" value="{{ $formSource }}">
                <div class="modal-body">
                    <div id="{{ $modalId }}_client_errors" class="alert alert-danger famledger-contact-client-errors" style="display: none;" role="alert" aria-live="polite"></div>
                    @if ($errors->any() && old('_contact_form_source') === $formSource)
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="row landing-contact-field-row">
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $nameId }}">{{ __('Full name') }}</label>
                                <input type="text" class="form-control" id="{{ $nameId }}" name="name" value="{{ old('_contact_form_source') === $formSource ? old('name') : '' }}" required autocomplete="name">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $emailId }}">{{ __('Email') }}</label>
                                <input type="email" class="form-control" id="{{ $emailId }}" name="email" value="{{ old('_contact_form_source') === $formSource ? old('email') : '' }}" required autocomplete="email">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $phoneId }}">{{ __('Phone number') }}</label>
                                <input type="text" class="form-control" id="{{ $phoneId }}" name="phone" value="{{ old('_contact_form_source') === $formSource ? old('phone') : '' }}" required autocomplete="tel">
                            </div>
                        </div>
                    </div>
                    <div class="row landing-contact-field-row">
                        <div class="col-xs-12">
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label for="{{ $quillId }}" id="{{ $lblQuillId }}">{{ __('Message') }}</label>
                                <div id="{{ $quillId }}" class="landing-contact-quill-wrap" aria-labelledby="{{ $lblQuillId }}"></div>
                                <textarea id="{{ $hiddenMessageId }}" name="message" class="sr-only" tabindex="-1" aria-hidden="true">{{ old('_contact_form_source') === $formSource ? old('message') : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @if ($useMath && $mathA >= 1 && $mathB >= 1)
                        <div class="row landing-contact-field-row">
                            <div class="col-xs-12 col-sm-6">
                                <div class="form-group landing-contact-math-captcha" style="margin-bottom: 8px;">
                                    <label for="{{ $mathAnswerId }}">{{ __('What is :a + :b?', ['a' => $mathA, 'b' => $mathB]) }}</label>
                                    <input
                                        type="number"
                                        class="form-control landing-contact-math-input"
                                        id="{{ $mathAnswerId }}"
                                        name="contact_captcha_answer"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        required
                                        min="0"
                                        max="200"
                                    >
                                    @error('contact_captcha_answer')
                                        <p class="text-danger small" style="margin-top: 8px;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (filled($siteKey))
                        <div class="row landing-contact-field-row">
                            <div class="col-xs-12">
                                <div class="form-group landing-recaptcha-group" style="margin-bottom: 8px;">
                                    <label class="sr-only">{{ __('Security verification') }}</label>
                                    <div id="{{ $recaptchaId }}" class="landing-recaptcha-wrap"></div>
                                    @error('g-recaptcha-response')
                                        <p class="text-danger small" style="margin-top: 8px;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="{{ $closeButtonClass }}" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="{{ $submitButtonClass }}">{{ __('Send message') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
