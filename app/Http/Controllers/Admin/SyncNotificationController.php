<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SyncNotification;

class SyncNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = SyncNotification::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'resolved') {
                $query->where('resolved', true);
            } elseif ($request->input('status') === 'pending') {
                $query->where('resolved', false);
            }
        }

        $notifications = $query->paginate(20)->withQueryString();
        
        // Get unique types for filter dropdown
        $types = SyncNotification::select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.sync_notifications.index', compact('notifications', 'types'));
    }

    public function resolve($id)
    {
        $notification = SyncNotification::findOrFail($id);
        $notification->update(['resolved' => true]);
        
        return redirect()->back()->with('success', 'Notification marked as resolved.');
    }
    
    public function destroy($id)
    {
        $notification = SyncNotification::findOrFail($id);
        $notification->delete();
        
        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
