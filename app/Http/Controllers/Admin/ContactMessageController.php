<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('contact-messages.view'), 403);
        return view('admin.messages.index', [
            'messages'    => ContactMessage::latest()->paginate(12),
            'unreadCount' => ContactMessage::where('status', 'new')->count(),
        ]);
    }

    public function show(ContactMessage $message)
    {
        abort_unless(auth()->user()->hasPermission('contact-messages.view'), 403);
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
            $message->refresh();
        }

        return view('admin.messages.show', [
            'message'     => $message,
            'unreadCount' => ContactMessage::where('status', 'new')->count(),
        ]);
    }

    public function markAsRead(ContactMessage $message)
    {
        abort_unless(auth()->user()->hasPermission('contact-messages.edit'), 403);
        if ($message->status !== 'read') {
            $message->update(['status' => 'read']);
        }

        return back()->with('success', 'Message marked as read.');
    }

    public function destroy(ContactMessage $message)
    {
        abort_unless(auth()->user()->hasPermission('contact-messages.delete'), 403);
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted successfully.');
    }
}
