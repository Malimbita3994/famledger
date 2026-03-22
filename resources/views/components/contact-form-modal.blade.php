{{--
  Reusable contact modal: name, email, phone, rich message (Quill), spam protection (math question by default or optional reCAPTCHA v2).
  POSTs to /contact by default (same-origin relative URL so 127.0.0.1 vs localhost matches APP_URL).

  variant="view" — same Bootstrap 3 shell and field layout, read-only; for admin contact message detail (requires :contact-message).

  Requirements on the page:
  - Bootstrap 3 + jQuery (before this modal’s scripts)
  - Quill 1.3.x + quill.snow.css when variant is form (landing uses public/vendor/quill)
  - Optional: window.__recaptchaSiteKey + google recaptcha api.js when CONTACT_CAPTCHA_DRIVER=recaptcha

  Usage (landing form):
    <x-contact-form-modal />
    <x-contact-form-modal modal-id="helpContactModal" :open-on-load="$errors->any() && old('_contact_form_source') === 'help'" />

  Usage (admin message detail — load famledger-bootstrap3-modal-slim.css, NOT full bootstrap.min.css; jquery.js, bootstrap.min.js):
    <x-contact-form-modal variant="view" :contact-message="$contact_message" modal-id="adminContactMessageModal" :open-on-load="true" />

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
    'variant' => 'form',
    'contactMessage' => null,
])

@php
    $isView = ($variant === 'view');
    if ($isView && ! $contactMessage instanceof \App\Models\ContactMessage) {
        throw new \InvalidArgumentException('contact-form-modal: pass an App\Models\ContactMessage as contactMessage when variant is "view".');
    }
    if ($isView) {
        $titleText = $title ?? __('Contact message');
    } else {
        $titleText = $title ?? __('Talk to the FamLedger team');
    }
    $actionUrl = $action ?? route('contact.store', absolute: false);
    $captchaDriverResolved = strtolower((string) ($captchaDriver ?? config('services.contact_captcha.driver', 'math')));
    if (! in_array($captchaDriverResolved, ['math', 'recaptcha', 'none'], true)) {
        $captchaDriverResolved = 'math';
    }
    $useRecaptcha = ! $isView && $captchaDriverResolved === 'recaptcha';
    $useMath = ! $isView && $captchaDriverResolved === 'math';
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
    class="modal fade famledger-contact-form-modal {{ $isView ? 'famledger-contact-form-modal--view' : '' }}"
    id="{{ $modalId }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $titleId }}"
    data-modal-variant="{{ $isView ? 'view' : 'form' }}"
    data-open-on-load="{{ $openFlag ? '1' : '0' }}"
    @if (! $isView)
    data-form-id="{{ $resolvedFormId }}"
    data-quill-container-id="{{ $quillId }}"
    data-hidden-message-id="{{ $hiddenMessageId }}"
    data-recaptcha-container-id="{{ filled($siteKey) ? $recaptchaId : '' }}"
    data-captcha-driver="{{ $captchaDriverResolved }}"
    @endif
>
    <div class="modal-dialog landing-contact-modal-wide" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="{{ $titleId }}">{{ $titleText }}</h4>
            </div>
            @if ($isView)
                @php
                    /** @var \App\Models\ContactMessage $contactMessage */
                    $m = $contactMessage;
                @endphp
                <div class="modal-body">
                    <p class="text-muted small" style="margin-top: -6px; margin-bottom: 14px;">
                        {{ $m->created_at->format('M j, Y \a\t H:i') }}
                        @if ($m->read_at)
                            · <span class="text-success">{{ __('Read') }} {{ $m->read_at->format('M j') }}</span>
                        @else
                            · <span class="text-warning">{{ __('New') }}</span>
                        @endif
                    </p>
                    <div class="row landing-contact-field-row">
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $nameId }}">{{ __('Full name') }}</label>
                                <input type="text" class="form-control" id="{{ $nameId }}" value="{{ $m->name }}" readonly autocomplete="off">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $emailId }}">{{ __('Email') }}</label>
                                <input type="text" class="form-control" id="{{ $emailId }}" value="{{ $m->email }}" readonly autocomplete="off">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <label for="{{ $phoneId }}">{{ __('Phone number') }}</label>
                                <input type="text" class="form-control" id="{{ $phoneId }}" value="{{ $m->phone ?? '—' }}" readonly autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row landing-contact-field-row">
                        <div class="col-xs-12">
                            <div class="form-group" style="margin-bottom: 10px;">
                                <span class="control-label" id="{{ $lblQuillId }}" style="display:block; margin-bottom: 6px; font-weight: 700;">{{ __('Message') }}</span>
                                <div class="landing-contact-quill-wrap landing-contact-message-view-html" aria-labelledby="{{ $lblQuillId }}" role="region">
                                    {!! \Stevebauman\Purify\Facades\Purify::config('notification_faq')->clean($m->message) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Back to list') }}</button>
                    {{-- Relative URLs: avoid APP_URL host (e.g. localhost) differing from browser host (127.0.0.1), which drops session/CSRF on POST --}}
                    <form action="{{ route('admin.contact-messages.read-status', $m, false) }}" method="POST" class="inline-block" style="display: inline-block; margin: 0;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="state" value="{{ $m->read_at ? 'unread' : 'read' }}">
                        <button type="submit" class="btn btn-primary btn-sm">{{ $m->read_at ? __('Mark as unread') : __('Mark as read') }}</button>
                    </form>
                    <form
                        action="{{ route('admin.contact-messages.destroy', $m, false) }}"
                        method="POST"
                        class="inline-block js-confirm-delete"
                        style="display: inline-block; margin: 0;"
                        data-confirm-title="{{ __('Delete message?') }}"
                        data-confirm-message="{{ __('This action cannot be undone.') }}"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            @else
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
            @endif
        </div>
    </div>
</div>
