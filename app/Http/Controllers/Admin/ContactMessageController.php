<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%");
            });
        }

        if ($request->filled('read')) {
            if ($request->read === 'read') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $messages = $query->paginate(15)->withQueryString();

        $contactStats = [
            'total' => ContactMessage::query()->count(),
            'unread' => ContactMessage::query()->whereNull('read_at')->count(),
            'read' => ContactMessage::query()->whereNotNull('read_at')->count(),
            'last_7_days' => ContactMessage::query()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $sampleId = ContactMessage::query()->orderByDesc('id')->value('id') ?? 1;
        $contactModalUrlTemplate = preg_replace(
            '#/'.preg_quote((string) $sampleId, '#').'/modal$#',
            '/__ID__/modal',
            route('admin.contact-messages.modal', ['contact_message' => $sampleId], false)
        );

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'contactStats' => $contactStats,
            'openContactMessageId' => $request->integer('open') ?: null,
            'contactModalUrlTemplate' => $contactModalUrlTemplate,
        ]);
    }

    /**
     * HTML fragment: Bootstrap contact modal (view variant) for AJAX injection on the index page.
     */
    public function modal(ContactMessage $contact_message): \Illuminate\Http\Response
    {
        // Do not mark read here: this endpoint is loaded via AJAX and the list row would stay "New"
        // until a full page reload. Use "Mark as read" in the modal (redirect refreshes the table).

        $html = Blade::render(
            '<x-contact-form-modal variant="view" :contact-message="$message" modal-id="adminContactMessageModal" :open-on-load="false" />',
            ['message' => $contact_message]
        );

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function show(ContactMessage $contact_message)
    {
        $contact_message->markAsRead();

        return redirect()
            ->route('admin.contact-messages.index', ['open' => $contact_message->id]);
    }

    public function updateReadStatus(Request $request, ContactMessage $contact_message)
    {
        $state = $request->input('state', 'read');

        if ($state === 'unread') {
            $contact_message->markAsUnread();
        } else {
            $contact_message->markAsRead();
        }

        return redirect()
            ->route('admin.contact-messages.index', ['open' => $contact_message->id])
            ->with('success', __('Message status updated.'));
    }

    public function destroy(ContactMessage $contact_message)
    {
        $contact_message->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Message deleted.');
    }
}
