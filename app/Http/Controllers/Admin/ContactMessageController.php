<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');

        $query = DB::table('contact_messages')
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'subject',
                'status',
                'created_at',
            ]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['new', 'read', 'replied', 'closed'], true)) {
            $query->where('status', $status);
        }

        $messages = $query
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => DB::table('contact_messages')->count(),
            'new' => DB::table('contact_messages')->where('status', 'new')->count(),
            'read' => DB::table('contact_messages')->where('status', 'read')->count(),
            'replied' => DB::table('contact_messages')->where('status', 'replied')->count(),
            'closed' => DB::table('contact_messages')->where('status', 'closed')->count(),
        ];

        return view('admin.messages.index', compact(
            'messages',
            'summary',
            'search',
            'status'
        ));
    }

    public function show(int $message): View
    {
        $message = DB::table('contact_messages')
            ->where('id', $message)
            ->first();

        abort_if(!$message, 404);

        if ($message->status === 'new') {
            DB::table('contact_messages')
                ->where('id', $message->id)
                ->where('status', 'new')
                ->update([
                    'status' => 'read',
                    'updated_at' => now(),
                ]);

            $message->status = 'read';
        }

        return view('admin.messages.show', compact('message'));
    }

    public function update(Request $request, int $message): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,replied,closed'],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $exists = DB::table('contact_messages')
            ->where('id', $message)
            ->exists();

        abort_if(!$exists, 404);

        DB::table('contact_messages')
            ->where('id', $message)
            ->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.messages.show', $message)
            ->with('success', 'Message updated successfully.');
    }
}
