<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index()
    {
        $myPendingTasks         = Auth::user()->assignedTasks()->pending()->count();
        $totalPendingTasks      = Task::pending()->count();

        $myProgressTasks        = Auth::user()->assignedTasks()->progress()->count();
        $totalProgressTasks     = Task::progress()->count();

        $myCompletedTasks       = Auth::user()->assignedTasks()->completed()->count();
        $totalCompletedTasks    = Task::completed()->count();

        $activeTasks = TaskResource::collection(Auth::user()->assignedTasks()->whereIn('status', ['pending', 'in_progress'])->limit(10)->get());

        return Inertia::render('Dashboard', compact([
            'myPendingTasks',
            'totalPendingTasks',
            'myProgressTasks',
            'totalProgressTasks',
            'myCompletedTasks',
            'totalCompletedTasks',
            'activeTasks',
        ]));
    }
}
