<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = $request->input('query');
        $user = Auth::user();
        $suggestions = collect();

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $folderQuery = Folder::query();
        $fileLinkQuery = FileLink::query();
        $accessibleAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin) {
            $folderQuery->whereIn('area_id', $accessibleAreaIds);
            $fileLinkQuery->whereHas('folder', function($q) use ($accessibleAreaIds) {
                $q->whereIn('area_id', $accessibleAreaIds);
            });


        } elseif ($user->isClient()) {
            $folderQuery->whereHas('usersWithAccess', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
            $fileLinkQuery->whereHas('folder', function($q) use ($user) {
                $q->whereHas('usersWithAccess', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                });
            });

        } else {
            $folderQuery->whereIn('area_id', $accessibleAreaIds)
                        ->whereHas('usersWithAccess', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
            $fileLinkQuery->whereHas('folder', function($q) use ($user, $accessibleAreaIds) {
                $q->whereIn('area_id', $accessibleAreaIds)
                ->whereHas('usersWithAccess', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                });
            });
        }

        $folders = $folderQuery->where('name', 'like', '%' . $query . '%')
                               ->with('parent') 
                               ->limit(5)
                               ->get()
                               ->map(function ($folder) {
                                   return [
                                       'id' => $folder->id,
                                       'name' => $folder->name,
                                       'type' => 'folder',
                                       'area' => $folder->area->name ?? 'N/A',
                                       'folder_id' => null,
                                       'full_path' => $folder->full_path,
                                   ];
                               });
        $suggestions = $suggestions->concat($folders);

        $fileLinks = $fileLinkQuery->where('name', 'like', '%' . $query . '%')
                                   ->with('folder.area', 'folder.parent') 
                                   ->limit(5)
                                   ->get()
                                   ->map(function ($fileLink) {
                                       return [
                                           'id' => $fileLink->id,
                                           'name' => $fileLink->name,
                                           'type' => $fileLink->type, 
                                           'url' => $fileLink->type === 'link' ? $fileLink->url : null,
                                           'area' => $fileLink->folder->area->name ?? 'N/A',
                                           'folder_id' => $fileLink->folder_id,
                                           'full_path' => $fileLink->full_path,
                                       ];
                                   });
        $suggestions = $suggestions->concat($fileLinks);

        return response()->json($suggestions->sortBy('name')->values()->all());
    }

    public function getEmailRecipients(Request $request)
    {
        $users = \App\Models\User::where('is_client', false)
                                ->orderBy('name')
                                ->get(['name', 'email']);

        return response()->json($users);
    }
    
}