<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get filter parameters from the request
        $actionTypeFilter = $request->input('action_type');
        $entityTypeFilter = $request->input('entity_type');
        $userIdFilter = $request->input('user_id');
        $startDateFilter = $request->input('start_date');
        $endDateFilter = $request->input('end_date');
        $searchFilter = $request->input('search');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc'); // Default DESC

        // Build the query
        $query = ActivityLog::with('user');

        // Apply filters
        $query->when($actionTypeFilter, function ($q) use ($actionTypeFilter) {
            return $q->where('action_type', $actionTypeFilter);
        });

        $query->when($entityTypeFilter, function ($q) use ($entityTypeFilter) {
            return $q->where('entity_type', $entityTypeFilter);
        });

        $query->when($userIdFilter, function ($q) use ($userIdFilter) {
            return $q->where('user_id', $userIdFilter);
        });

        // Apply date range filter
        if ($startDateFilter && $endDateFilter) {
            $query->whereBetween('created_at', [$startDateFilter . ' 00:00:00', $endDateFilter . ' 23:59:59']);
        } elseif ($startDateFilter) {
            $query->whereDate('created_at', '>=', $startDateFilter);
        } elseif ($endDateFilter) {
            $query->whereDate('created_at', '<=', $endDateFilter);
        }

        // Apply search filter (search in JSON details)
        $query->when($searchFilter, function ($q) use ($searchFilter) {
            return $q->where(function ($subQuery) use ($searchFilter) {
                // Search in campaign_name
                $subQuery->where('campaign_name', 'like', '%' . $searchFilter . '%')
                    // Or in ad_group_name
                    ->orWhere('ad_group_name', 'like', '%' . $searchFilter . '%')
                    // Or in JSON details (keyword)
                    ->orWhereRaw("JSON_EXTRACT(details, '$.keyword') LIKE ?", ['%' . $searchFilter . '%']);
            });
        });

        // Apply dynamic sorting
        $allowedSortColumns = ['created_at', 'action_type', 'entity_type', 'user_id'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            // Default sort if no valid sort_by is provided
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $activityLogs = $query->paginate(50);

        // Get distinct values for filter dropdowns
        $actionTypes = ActivityLog::select('action_type')
                                  ->distinct()
                                  ->orderBy('action_type')
                                  ->pluck('action_type');

        $entityTypes = ActivityLog::select('entity_type')
                                  ->distinct()
                                  ->orderBy('entity_type')
                                  ->pluck('entity_type');

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        // Return the view with data
        return view('activity_logs.index', [
            'activityLogs' => $activityLogs,
            'actionTypes' => $actionTypes,
            'entityTypes' => $entityTypes,
            'users' => $users,
            'filters' => $request->only(['action_type', 'entity_type', 'user_id', 'start_date', 'end_date', 'search', 'sort_by', 'sort_direction']), // Pass all filters back to view
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param ActivityLog $activityLog
     * @return View
     */
    public function show(ActivityLog $activityLog): View
    {
        // Load the user relationship
        $activityLog->load('user');

        return view('activity_logs.show', [
            'activityLog' => $activityLog,
        ]);
    }
}
