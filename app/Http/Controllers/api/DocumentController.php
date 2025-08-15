<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;

class DocumentController extends Controller
{
    //
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:102400', // max 100MB
            'project_id' => 'required|integer',
        ]);

        $file = $request->file('document');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents', $filename, 'public');

        $document = Document::create([
            'project_id'  => $request->project_id,
            'title'       => $request->input('title', $file->getClientOriginalName()),
            'content'     => $request->input('content', ''),
            'file_url'    => $path,
            'manager_view' => $request->input('manager_view', false),
            'created_by'  => Auth::id(),
            'updated_by'  => Auth::id(),
        ]);

        if ($document) {
            // Log activity
            ActivityLogger::log(
                'add',
                Auth::id(),
                'Tải lên tài liệu: ' . $document->title . ' trong dự án: ' . ($document->project)->project_name,
                ($document->project)->workspace_id,
                $document->project_id
            );
        }

        return response()->json([
            'message' => 'Upload thành công',
            'document' => $document,
            'success' => true,
        ]);
    }

    function getDocumentsByProjectId(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        $documents = Document::where('project_id', $request->project_id)
            ->when($request->search, fn($q) => $q->where('title', $request->search))
            ->select()
            ->paginate($request->perPage ?? 15);


        $documents->transform(function ($item) {
            $item->full_url = Storage::url($item->file_url); // trả về /storage/...
            return $item;
        });

        return response()->json([
            'documents' => $documents,
            'success' => true,
        ]);
    }

    function deleteDocument(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:tblDocuments,id'
        ]);
        $document = Document::findOrFail($request->file_id);

        // Kiểm tra quyền sở hữu
        if ($document->created_by !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa tài liệu này',
                'success' => false,
            ], 403);
        }

        Storage::disk('public')->delete($document->file_url);

        $document->delete();

        // Log activity
        ActivityLogger::log(
            'delete',
            Auth::id(),
            'Xóa tài liệu: ' . $document->title . ' trong dự án: ' . ($document->project)->project_name,
            ($document->project)->workspace_id,
            $document->project_id
        );

        return response()->json([
            'message' => 'Tài liệu đã được xóa thành công',
            'success' => true,
        ]);
    }
}
