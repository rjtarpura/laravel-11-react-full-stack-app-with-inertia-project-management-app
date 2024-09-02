<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Task::query();

        if(request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        if(request('status')) {
            $query->where('status', request('status'));
        }

        $sortField      = request('sort_field', 'created_at');
        $sortDirection  = request('sort_direction', 'DESC');

        $tasks = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return Inertia::render('Task/Index', [
            'tasks'         => TaskResource::collection($tasks),
            'queryParams'   => request()->query() ?: null,
            'success'       => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia("Task/Create", [
            'users'     => UserResource::collection(User::orderBy('name', 'asc')->get()),
            'projects'  => ProjectResource::collection(Project::orderBy('name', 'asc')->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $data['image'] ?? null;

        if($image) {
            $data['image_path'] = $image->store('task/' . Str::random(), 'public');
        }

        Task::create($data);

        return to_route('task.index')->with('success', 'Task was created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return Inertia::render('Task/Show', [
            'task'  => new TaskResource($task),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        return inertia('Task/Edit', [
            'task'      => new TaskResource($task),
            'users'     => UserResource::collection(User::orderBy('name', 'asc')->get()),
            'projects'  => ProjectResource::collection(Project::orderBy('name', 'asc')->get()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $data['image'] ?? null;

        if($image) {

            if($task->image_path) {
                Storage::disk('public')->deleteDirectory(dirname($task->image_path));
            }

            $data['image_path'] = $image->store('task/' . Str::random(), 'public');
        }

        $task->update($data);

        return to_route('task.index')->with('success', "Task \"$task->name\" was updated.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $name = $task->name;

        if($task->image_path) {
            Storage::disk('public')->deleteDirectory(dirname($task->image_path));
        }

        $task->delete();

        return to_route('task.index')->with('success', "Task \"$name\" was deleted.");
    }

    /**
     * Display the specified resource.
     */
    public function myTasks()
    {
        $query = Auth::user()->assignedTasks();

        if(request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        if(request('status')) {
            $query->where('status', request('status'));
        }

        $sortField      = request('sort_field', 'created_at');
        $sortDirection  = request('sort_direction', 'DESC');

        $tasks = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return Inertia::render('Task/Index', [
            'tasks'         => TaskResource::collection($tasks),
            'queryParams'   => request()->query() ?: null,
            'success'       => session('success'),
        ]);
    }
}
