<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Workflow;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function create(Request $request)
    {
        $organisationId = auth()->user()->organisation_id;
        $validated_data = $request->validate([
            'folder_name' => 'required|string|max:255',
        ]);
        Folder::create([
            'name' => $validated_data['folder_name'],
            'organisation_id' => $organisationId
        ]);
        return redirect()->route('create-workflow')->with('success', "Folder saved successfully");
    }
    public function delete($id)
    {
        $folder = Folder::findOrFail($id);

        // Update workflows that were in this folder
        Workflow::where('folder_id', $folder->id)
            ->update(['folder_id' => null]); // or set to a default folder id

        // Now delete the folder
        $folder->delete();

        return response()->json([
            'message' => 'Folder deleted successfully, workflows updated.',
            'folder' => $folder
        ], 200);
    }

    public function assign(Request $request)
    {
        $validated_data = $request->validate([
            'folder_id' => 'required|string|max:255',
            'id' => 'required|integer|max:255',
        ]);
        $workflow = Workflow::find($validated_data['id']);
        $workflow->folder_id = $validated_data['folder_id'];
        $workflow->save();
        return redirect()->route('create-workflow')->with('success', "workflow moved to folder successfully");
    }
    public function remove_workflow_from_folder(Request $request)
    {
        $validated_data = $request->validate([
            'workflow_id' => 'required',
        ]);
        $workflow = Workflow::find($validated_data['workflow_id']);
        $workflow->folder_id = null;
        $workflow->save();
        return response()->json([
            'workflow' => $workflow
        ], 200);
    }
    function get_folder_workflows($id)
    {

        $workflows = Workflow::where('folder_id', $id)->get();
        return response()->json([
            'workflows' => $workflows
        ], 200);
    }
}
