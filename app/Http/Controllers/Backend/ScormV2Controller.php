<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ModuleLecture;
use App\Services\ScormV2\ScormV2Importer;
use Illuminate\Http\Request;

class ScormV2Controller extends Controller
{
    public function upload(Request $request, ModuleLecture $lecture, ScormV2Importer $importer)
    {
        $request->validate([
            'scorm_zip' => ['required', 'file', 'mimes:zip', 'max:512000'], // 500 Mo
        ]);

        $data = $importer->importForLecture((int)$lecture->id, $request->file('scorm_zip'));

        $lecture->update([
            'scorm_folder'      => $data['scorm_folder'],
            'scorm_launch_path' => $data['scorm_launch_path'],
        ]);

        return back()->with('success', 'SCORM importé (V2).');
    }

    public function preview(ModuleLecture $lecture)
    {
        abort_unless($lecture->scorm_launch_path, 404);

        $src = asset('storage/' . $lecture->scorm_launch_path);

        return view('admin.backend.scorm_v2.preview', compact('lecture', 'src'));
    }
}
