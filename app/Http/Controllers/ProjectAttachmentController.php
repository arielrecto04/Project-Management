<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectAttachmentController extends Controller
{
    /**
     * Store new attachments for the project
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240' // 10MB max per file
        ]);

        $attachments = [];

        foreach ($request->file('files') as $file) {
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(
                "projects/attachments",
                $fileName,
                'public' // Changed to public disk
            );

            $attachments[] = $project->attachments()->create([
                'name' => $file->getClientOriginalName(),
                'path' =>  asset('/storage/' . $path),
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'user_id' => Auth::user()->id,
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getClientMimeType(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Download an attachment
     */
    public function download(Project $project, Attachment $attachment)
    {


        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->name
        );
    }

    /**
     * Delete an attachment
     */
    public function destroy(Project $project, Attachment $attachment)
    {


        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back();
    }
}
