<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], [
            'name' => 'full name',
            'email' => 'email address',
            'message' => 'message',
        ]);

        ContactMessage::create($validated);

        return redirect()->route('landing')->with('success', 'Thank you! Your message has been sent. We will get back to you soon.');
    }
}
