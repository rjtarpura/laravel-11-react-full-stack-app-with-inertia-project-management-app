<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Project::query();

        if(request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        if(request('status')) {
            $query->where('status', request('status'));
        }

        $sortField      = request('sort_field', 'created_at');
        $sortDirection  = request('sort_direction', 'DESC');

        $projects = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return Inertia::render('Project/Index', [
            'projects'      => ProjectResource::collection($projects),
            'queryParams'   => request()->query() ?: null,
            'success'       => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia("Project/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $data['image'] ?? null;

        if($image) {
            $data['image_path'] = $image->store('project/' . Str::random(), 'public');
        }

        Project::create($data);

        return to_route('project.index')->with('success', 'Project was created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $query = $project->tasks();

        if(request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        if(request('status')) {
            $query->where('status', request('status'));
        }

        $sortField      = request('sort_field', 'created_at');
        $sortDirection  = request('sort_direction', 'DESC');

        $tasks = $query->orderBy($sortField, $sortDirection)->paginate(10)->onEachSide(1);

        return Inertia::render('Project/Show', [
            'project'       => new ProjectResource($project),
            'tasks'         => TaskResource::collection($tasks),
            'queryParams'   => request()->query() ?: null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return inertia('Project/Edit', [
            'project'   => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $data['image'] ?? null;

        if($image) {

            if($project->image_path) {
                Storage::disk('public')->deleteDirectory(dirname($project->image_path));
            }

            $data['image_path'] = $image->store('project/' . Str::random(), 'public');
        }

        $project->update($data);

        return to_route('project.index')->with('success', "Project \"$project->name\" was updated.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $name = $project->name;

        if($project->image_path) {
            Storage::disk('public')->deleteDirectory(dirname($project->image_path));
        }

        $project->delete();

        return to_route('project.index')->with('success', "Project \"$name\" was deleted.");
    }
}