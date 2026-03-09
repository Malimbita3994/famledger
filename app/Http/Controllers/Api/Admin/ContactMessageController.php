<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    use AuthorizesAdmin;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

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

        $perPage = min((int) $request->get('per_page', 15), 50);
        $messages = $query->paginate($perPage);

        $items = $messages->getCollection()->map(fn (ContactMessage $m) => [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'message' => $m->message,
            'read_at' => $m->read_at?->toIso8601String(),
            'created_at' => $m->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'messages' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function show(ContactMessage $contact_message): JsonResponse
    {
        $this->authorizeAdmin();

        $contact_message->markAsRead();

        return response()->json([
            'id' => $contact_message->id,
            'name' => $contact_message->name,
            'email' => $contact_message->email,
            'message' => $contact_message->message,
            'read_at' => $contact_message->read_at?->toIso8601String(),
            'created_at' => $contact_message->created_at?->toIso8601String(),
        ]);
    }

    public function updateReadStatus(Request $request, ContactMessage $contact_message): JsonResponse
    {
        $this->authorizeAdmin();

        $state = $request->input('state', 'read');
        if ($state === 'unread') {
            $contact_message->update(['read_at' => null]);
        } else {
            $contact_message->markAsRead();
        }

        return response()->json([
            'id' => $contact_message->id,
            'read_at' => $contact_message->fresh()->read_at?->toIso8601String(),
        ]);
    }
}
