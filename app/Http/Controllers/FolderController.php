<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Workflow;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function create(Request $request){
        $organisationId = auth()->user()->organisation_id;
        $validated_data = $request->validate([
            'folder_name' => 'required|string|max:255',
        ]);
        Folder::create([
            'name'=>$validated_data['folder_name'],
            'organisation_id'=>$organisationId
        ]);
        return redirect()->route('create-workflow')->with('success', "Folder saved successfully");
    }
    public function delete($id){
        $folder=Folder::find($id);
        $folder->delete();
        return response()->json([
            'folder' => $folder
        ], 200);
    }
    public function assign(Request $request){
        $validated_data = $request->validate([
            'folder' => 'required|string|max:255',
            'id' => 'required|integer|max:255',
        ]);
        $workflow=Workflow::find($validated_data['id']);
        $workflow->folder_id=$validated_data['folder'];
        $workflow->save();
        return redirect()->route('create-workflow')->with('success', "workflow moved to folder successfully");
    }
    function get_folder_workflows($id){
       
        $workflows = Workflow::where('folder_id', $id)->get();
        return response()->json([
            'workflows' => $workflows
        ], 200);
    }
}
