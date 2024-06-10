<?php

namespace App\Http\Controllers\RestApi;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'nullable|in:pending,completed',
                'search' => 'nullable|string',
                'take' => 'nullable|integer',
                'page' => 'nullable|integer'
            ]);

            $take = $request->take ?? 10;
            $page = $request->page ?? 1;

            $tasks = Task::when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
                ->when($request->search, function ($query, $search) {
                    return $query->whereLike(['title', 'description'], $search);
                })
                ->with('user:id,name,email')
                ->orderBy('id', 'desc')
                ->paginate($take);

            return $this->sendResponse($tasks, 'Tasks retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,completed',
                'due_date' => 'nullable|date',
                'assigned_to' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $task = Task::create([
                'assigned_to' => $request->assigned_to,
                'assigned_by' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'due_date' => $request->due_date
            ]);

            return $this->sendResponse($task, 'Task created successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $task = Task::with('user:id,name,email')->find($id);
            if (!$task) {
                return $this->sendError('Task not found.', 'Task not found.', 404);
            }

            return $this->sendResponse($task, 'Task retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,completed',
                'due_date' => 'nullable|date',
                'assigned_to' => 'sometimes|required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $task = Task::find($id);
            if (!$task) {
                return $this->sendError('Task not found.', 'Task not found.', 404);
            }

            $task->update($request->only('title', 'description', 'status', 'due_date'));
            return $this->sendResponse($task, 'Task updated successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::find($id);
            if (!$task) {
                return $this->sendError('Task not found.', 'Task not found.', 404);
            }

            $task->delete();
            return $this->sendResponse(null, 'Task deleted successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Server Error.', $th->getMessage(), 500);
        }
    }
}
