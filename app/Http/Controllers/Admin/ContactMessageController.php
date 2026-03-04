<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

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

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contact_message)
    {
        $contact_message->markAsRead();

        return view('admin.contact-messages.show', compact('contact_message'));
    }

    public function updateReadStatus(Request $request, ContactMessage $contact_message)
    {
        $state = $request->input('state', 'read');

        if ($state === 'unread') {
            $contact_message->update(['read_at' => null]);
        } else {
            $contact_message->markAsRead();
        }

        return redirect()
            ->route('admin.contact-messages.show', $contact_message)
            ->with('success', 'Message status updated.');
    }

    public function destroy(ContactMessage $contact_message)
    {
        $contact_message->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Message deleted.');
    }
}
