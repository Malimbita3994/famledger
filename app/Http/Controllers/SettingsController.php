<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ExpenseCategory;
use App\Models\Family;
use App\Models\FamilyRole;
use App\Models\IncomeCategory;
use App\Models\NotificationFaq;
use App\Models\NotificationPageContent;
use App\Models\NotificationSupportContact;
use App\Models\SystemLookup;
use App\Models\User;
use App\Support\CurrentFamilyResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stevebauman\Purify\Facades\Purify;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings overview page.
     */
    public function index(Request $request): View
    {
        return view('settings.index', [
            'currentFamily' => CurrentFamilyResolver::family($request),
        ]);
    }

    /**
     * Active family for sidebar links (session, then primary membership).
     */
    protected function resolveCurrentFamily(Request $request): ?Family
    {
        return CurrentFamilyResolver::family($request);
    }

    /**
     * Categories & lookup settings (system-wide lookups).
     */
    public function categories(): View
    {
        $currencies = config('currencies', []);
        $defaultCurrency = $currencies['default'] ?? null;

        $incomeCategories = IncomeCategory::defaults();
        $expenseCategories = ExpenseCategory::defaults();
        $familyRoles = FamilyRole::orderBy('id')->get();
        $customLookups = SystemLookup::orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return view('settings.categories', [
            'currencies' => $currencies,
            'defaultCurrency' => $defaultCurrency,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'familyRoles' => $familyRoles,
            'customLookups' => $customLookups,
        ]);
    }

    /**
     * Quick-create a lookup item (income, expense, or role) from the top Add button.
     */
    public function storeLookup(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $rawType = trim($validated['type']);
        $normalized = mb_strtolower($rawType);

        // Special-case known lookup types so they integrate with existing tables.
        switch ($normalized) {
            case 'income':
            case 'income category':
            case 'income':
                IncomeCategory::firstOrCreate(
                    ['name' => $validated['name'], 'family_id' => null],
                    ['name' => $validated['name']]
                );
                $message = __('Income category added.');
                break;

            case 'expense':
            case 'expense category':
                ExpenseCategory::firstOrCreate(
                    ['name' => $validated['name'], 'family_id' => null],
                    ['name' => $validated['name']]
                );
                $message = __('Expense category added.');
                break;

            case 'role':
            case 'family role':
                FamilyRole::firstOrCreate(
                    ['name' => $validated['name']],
                    [
                        'description' => $validated['description'] ?? null,
                        'is_system' => false,
                    ]
                );
                $message = __('Family role added.');
                break;

            default:
                // Generic lookup group stored in system_lookups
                SystemLookup::create([
                    'group' => $rawType,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'sort_order' => 0,
                    'is_active' => true,
                ]);
                $message = __('Lookup created.');
        }

        return redirect()
            ->route('settings.categories')
            ->with('success', $message);
    }

    /**
     * Update a generic system lookup (group or name/description).
     */
    public function updateLookup(Request $request, SystemLookup $systemLookup)
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $systemLookup->update([
            'group' => $validated['group'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Lookup updated.'));
    }

    /**
     * Delete a generic system lookup.
     */
    public function destroyLookup(SystemLookup $systemLookup)
    {
        $systemLookup->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Lookup deleted.'));
    }

    /**
     * Create a new default income category.
     */
    public function storeIncomeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        IncomeCategory::firstOrCreate(
            ['name' => $validated['name'], 'family_id' => null],
            ['name' => $validated['name']]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category added.'));
    }

    /**
     * Update a default income category name.
     */
    public function updateIncomeCategory(Request $request, IncomeCategory $incomeCategory)
    {
        abort_unless($incomeCategory->family_id === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $incomeCategory->update(['name' => $validated['name']]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category updated.'));
    }

    /**
     * Delete a default income category.
     */
    public function destroyIncomeCategory(IncomeCategory $incomeCategory)
    {
        abort_unless($incomeCategory->family_id === null, 404);

        $incomeCategory->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Income category deleted.'));
    }

    /**
     * Create a new default expense category.
     */
    public function storeExpenseCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        ExpenseCategory::firstOrCreate(
            ['name' => $validated['name'], 'family_id' => null],
            ['name' => $validated['name']]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category added.'));
    }

    /**
     * Update a default expense category name.
     */
    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_unless($expenseCategory->family_id === null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $expenseCategory->update(['name' => $validated['name']]);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category updated.'));
    }

    /**
     * Delete a default expense category.
     */
    public function destroyExpenseCategory(ExpenseCategory $expenseCategory)
    {
        abort_unless($expenseCategory->family_id === null, 404);

        $expenseCategory->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Expense category deleted.'));
    }

    /**
     * Create a new (custom) family role.
     */
    public function storeFamilyRole(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        FamilyRole::firstOrCreate(
            ['name' => $validated['name']],
            [
                'description' => $validated['description'] ?? null,
                'is_system' => false,
            ]
        );

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role added.'));
    }

    /**
     * Update an existing family role (description only for system roles).
     */
    public function updateFamilyRole(Request $request, FamilyRole $familyRole)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $update = [
            'description' => $validated['description'] ?? null,
        ];

        if (! $familyRole->is_system) {
            $update['name'] = $validated['name'];
        }

        $familyRole->update($update);

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role updated.'));
    }

    /**
     * Delete a custom family role (system roles cannot be deleted).
     */
    public function destroyFamilyRole(FamilyRole $familyRole)
    {
        if ($familyRole->is_system) {
            return redirect()
                ->route('settings.categories')
                ->with('error', __('System roles cannot be deleted.'));
        }

        $familyRole->delete();

        return redirect()
            ->route('settings.categories')
            ->with('success', __('Family role deleted.'));
    }

    /**
     * Notifications settings (placeholder for now).
     */
    public function notifications(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('settings.notifications', [
            'prefs' => $user->mergedNotificationPreferences(),
            'currentFamily' => $this->resolveCurrentFamily($request),
            'notificationFaqs' => NotificationFaq::query()->ordered()->get(),
            'notificationFaqsPublic' => NotificationFaq::query()->active()->ordered()->get(),
            'notificationSupportContacts' => NotificationSupportContact::query()->ordered()->get(),
            'notificationPageContent' => NotificationPageContent::singleton(),
            'canManageNotificationPage' => $user->hasRole('Super Admin'),
        ]);
    }

    public function storeNotificationFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:65000'],
            'answer' => ['required', 'string', 'max:65000'],
            'group_label' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        [$question, $answer] = $this->purifyAndValidateNotificationFaq(
            $validated['question'],
            $validated['answer']
        );

        $groupLabel = $this->normalizeNotificationFaqGroupLabel($validated['group_label'] ?? null);

        NotificationFaq::query()->create([
            'question' => $question,
            'answer' => $answer,
            'group_label' => $groupLabel,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('FAQ entry added.'));
    }

    public function updateNotificationFaq(Request $request, NotificationFaq $notification_faq): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:65000'],
            'answer' => ['required', 'string', 'max:65000'],
            'group_label' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        [$question, $answer] = $this->purifyAndValidateNotificationFaq(
            $validated['question'],
            $validated['answer']
        );

        $groupLabel = $this->normalizeNotificationFaqGroupLabel($validated['group_label'] ?? null);

        $notification_faq->update([
            'question' => $question,
            'answer' => $answer,
            'group_label' => $groupLabel,
            'sort_order' => $validated['sort_order'] ?? $notification_faq->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('FAQ entry updated.'));
    }

    public function destroyNotificationFaq(NotificationFaq $notification_faq): RedirectResponse
    {
        $notification_faq->delete();

        return back()->with('success', __('FAQ entry deleted.'));
    }

    public function storeNotificationSupportContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65000'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $body = $this->purifySupportContactBody($validated['body']);

        NotificationSupportContact::query()->create([
            'title' => $validated['title'],
            'body' => $body,
            'link_url' => $validated['link_url'] ?: null,
            'link_label' => $validated['link_label'] ?: null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()
            ->with('success', __('Contact support entry added.'))
            ->with('open_notifications_tab', 'notif_tab_contact');
    }

    public function updateNotificationSupportContact(Request $request, NotificationSupportContact $notification_support_contact): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65000'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $body = $this->purifySupportContactBody($validated['body']);

        $notification_support_contact->update([
            'title' => $validated['title'],
            'body' => $body,
            'link_url' => $validated['link_url'] ?: null,
            'link_label' => $validated['link_label'] ?: null,
            'sort_order' => $validated['sort_order'] ?? $notification_support_contact->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()
            ->with('success', __('Contact support entry updated.'))
            ->with('open_notifications_tab', 'notif_tab_contact');
    }

    public function destroyNotificationSupportContact(NotificationSupportContact $notification_support_contact): RedirectResponse
    {
        $notification_support_contact->delete();

        return back()
            ->with('success', __('Contact support entry deleted.'))
            ->with('open_notifications_tab', 'notif_tab_contact');
    }

    public function updateNotificationPageContent(Request $request): RedirectResponse
    {
        $content = NotificationPageContent::singleton();
        $section = $request->input('page_content_section');

        if ($section === 'dnd') {
            $validated = $request->validate([
                'dnd_intro' => ['required', 'string', 'max:20000'],
                'dnd_learn_more_url' => ['nullable', 'string', 'max:2048'],
                'dnd_learn_more_label' => ['nullable', 'string', 'max:255'],
            ]);

            $content->update([
                'dnd_intro' => $validated['dnd_intro'],
                'dnd_learn_more_url' => $validated['dnd_learn_more_url'] ?: null,
                'dnd_learn_more_label' => $validated['dnd_learn_more_label'] ?: null,
            ]);

            return back()
                ->with('success', __('Do not disturb tab content updated.'))
                ->with('open_notifications_tab', 'notif_tab_dnd');
        }

        abort(422, 'Invalid page content section.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->input('action') === 'snooze_8') {
            $prefs = $user->mergedNotificationPreferences();
            $prefs['dnd_enabled'] = true;
            $prefs['dnd_until'] = now()->addHours(8)->toDateTimeString();
            $user->update(['notification_preferences' => $prefs]);

            return back()->with('success', __('Notifications paused for 8 hours.'));
        }

        if ($request->input('action') === 'dnd_clear') {
            $prefs = $user->mergedNotificationPreferences();
            $prefs['dnd_enabled'] = false;
            $prefs['dnd_until'] = null;
            $user->update(['notification_preferences' => $prefs]);

            return back()->with('success', __('Do not disturb is off.'));
        }

        $request->validate([
            'slack_webhook_url' => ['nullable', 'string', 'max:512', 'regex:/^https:\/\/.+/i'],
            'dnd_until' => ['nullable', 'date'],
        ], [
            'slack_webhook_url.regex' => __('Slack webhook must be a valid https URL.'),
        ]);

        $prefs = $user->mergedNotificationPreferences();
        $prefs['team_wide_alerts'] = $request->boolean('team_wide_alerts');
        $prefs['family_wide_alerts'] = $request->boolean('family_wide_alerts');
        $prefs['channel_email_enabled'] = $request->boolean('channel_email_enabled');
        $prefs['channel_mobile_enabled'] = $request->boolean('channel_mobile_enabled');
        $prefs['channel_desktop_enabled'] = $request->boolean('channel_desktop_enabled');
        $prefs['channel_slack_enabled'] = $request->boolean('channel_slack_enabled');
        $prefs['slack_webhook_url'] = $request->filled('slack_webhook_url') ? $request->input('slack_webhook_url') : null;
        $prefs['notify_task_assigned'] = $request->boolean('notify_task_assigned');
        $prefs['notify_budget_warning'] = $request->boolean('notify_budget_warning');
        $prefs['notify_invoice'] = $request->boolean('notify_invoice');
        $prefs['notify_feedback'] = $request->boolean('notify_feedback');
        $prefs['notify_collaboration'] = $request->boolean('notify_collaboration');
        $prefs['notify_meeting_reminder'] = $request->boolean('notify_meeting_reminder');
        $prefs['notify_status_change'] = $request->boolean('notify_status_change');
        $prefs['dnd_enabled'] = $request->boolean('dnd_enabled');
        $prefs['dnd_until'] = $request->filled('dnd_until') ? $request->date('dnd_until')->toDateTimeString() : null;

        $user->update(['notification_preferences' => $prefs]);

        return back()->with('success', __('Notification preferences saved.'));
    }

    /**
     * Global audit log (Super Admin / Auditor): whole system or filter by a family the user belongs to.
     */
    public function auditLog(Request $request): View
    {
        $logs = $this->auditLogBaseQuery($request)->paginate(25)->withQueryString();

        // Families the user belongs to (for scope dropdown: "Whole system" or pick a family)
        $families = $request->user()->families()->select('families.id as id', 'families.name as name')->orderBy('families.name')->get();

        return view('settings.audit-log', [
            'logs' => $logs,
            'families' => $families,
        ]);
    }

    /**
     * Export global audit log as PDF (same filters as the index page).
     */
    public function auditLogExportPdf(Request $request): Response
    {
        $logs = $this->auditLogBaseQuery($request)->limit(500)->get();
        $generatedAt = now()->format('Y-m-d H:i:s');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('settings.audit-log-pdf', [
            'logs' => $logs,
            'generatedAt' => $generatedAt,
            'filtersSummary' => $this->auditLogFiltersSummary($request),
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'audit-log-'.now()->format('Y-m-d-His').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export global audit log as CSV (same filters as the index page).
     */
    public function auditLogExportCsv(Request $request): StreamedResponse
    {
        $filename = 'audit-log-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('When (UTC)'),
                __('User name'),
                __('User email'),
                __('Family'),
                __('Type'),
                __('Area'),
                __('Action'),
                __('Description'),
                __('IP'),
            ]);

            $logs = $this->auditLogBaseQuery($request)->limit(5000)->get();

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('Y-m-d H:i:s') ?? '',
                    $log->user?->name ?? '',
                    $log->user?->email ?? '',
                    $log->family?->name ?? '',
                    $log->type,
                    $log->area,
                    $log->action,
                    $log->description ?? '',
                    $log->ip ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Builder<AuditLog>
     */
    protected function auditLogBaseQuery(Request $request): Builder
    {
        $query = AuditLog::with(['user:id,name,email', 'family:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('family_id')) {
            $familyId = (int) $request->input('family_id');
            $userFamilies = $request->user()->families()->pluck('families.id')->toArray();
            if (in_array($familyId, $userFamilies, true)) {
                $query->forFamily($familyId);
            }
        }

        if ($request->filled('type')) {
            if ($request->input('type') === AuditLog::TYPE_APPLICATION) {
                $query->application();
            } elseif ($request->input('type') === AuditLog::TYPE_DATABASE) {
                $query->database();
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        return $query;
    }

    protected function auditLogFiltersSummary(Request $request): string
    {
        $parts = [];
        $userFamilyIds = $request->user()->families()->pluck('families.id')->toArray();
        if ($request->filled('family_id')) {
            $familyId = (int) $request->input('family_id');
            if (in_array($familyId, $userFamilyIds, true)) {
                $name = Family::whereKey($familyId)->value('name');
                $parts[] = $name ? 'Family: '.$name : 'Family #'.$familyId;
            } else {
                $parts[] = 'Scope: Whole system';
            }
        } else {
            $parts[] = 'Scope: Whole system';
        }
        if ($request->filled('type')) {
            $parts[] = 'Type: '.$request->input('type');
        }
        if ($request->filled('from')) {
            $parts[] = 'From: '.$request->input('from');
        }
        if ($request->filled('to')) {
            $parts[] = 'To: '.$request->input('to');
        }

        return implode(' · ', $parts);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function purifyAndValidateNotificationFaq(string $question, string $answer): array
    {
        $purifier = Purify::config('notification_faq');
        $questionHtml = $purifier->clean($question);
        $answerHtml = $purifier->clean($answer);

        $qPlain = trim(html_entity_decode(strip_tags($questionHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $aPlain = trim(html_entity_decode(strip_tags($answerHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $errors = [];
        if ($qPlain === '') {
            $errors['question'] = [__('Please enter a question.')];
        }
        if ($aPlain === '') {
            $errors['answer'] = [__('Please enter an answer.')];
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if (mb_strlen($questionHtml) > 12000) {
            throw ValidationException::withMessages([
                'question' => [__('The question is too long after formatting.')],
            ]);
        }
        if (mb_strlen($answerHtml) > 62000) {
            throw ValidationException::withMessages([
                'answer' => [__('The answer is too long after formatting.')],
            ]);
        }

        return [$questionHtml, $answerHtml];
    }

    protected function normalizeNotificationFaqGroupLabel(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $t = trim(html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $t === '' ? null : mb_substr($t, 0, 120);
    }

    protected function purifySupportContactBody(string $body): string
    {
        $html = Purify::config('notification_faq')->clean($body);
        $plain = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($plain === '') {
            throw ValidationException::withMessages([
                'body' => [__('Please enter a description.')],
            ]);
        }
        if (mb_strlen($html) > 62000) {
            throw ValidationException::withMessages([
                'body' => [__('The description is too long after formatting.')],
            ]);
        }

        return $html;
    }
}
