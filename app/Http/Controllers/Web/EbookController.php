<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EbookController extends Controller
{
    /**
     * Download the free eBook
     *
     * @return BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(Request $request)
    {
        $filePath = config('ebook.file_path', 'ebooks/free-ebook.pdf');
        $fullPath = public_path($filePath);

        // Check if file exists
        if (! file_exists($fullPath)) {
            abort(404, 'eBook file not found');
        }

        // Return file download with appropriate headers
        return response()->download($fullPath, 'free-ebook.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
