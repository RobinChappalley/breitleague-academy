<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ModuleResource;
use App\Models\Module;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $module = Module::with('lessons.theories.questions.choices')->get();
        if ($module->count() > 0) {
            return moduleResource::collection($module);
        } else {
            return response()->json(['message' => 'No modules'], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Module $module)
    {
        $module->load('lessons.theories.questions.choices');
        return new ModuleResource($module);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Creating modules is not allowed on this endpoint.'
        ], 405);
    }

    public function update()
    {
        return response()->json([
            'message' => 'Updating modules is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Deleting modules is not allowed on this endpoint.'
        ], 405);
    }
}
