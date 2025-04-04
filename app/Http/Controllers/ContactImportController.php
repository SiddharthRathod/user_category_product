<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessCsvUpload;

class ContactImportController extends Controller
{
    
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
    
        // $file = $request->file('csv_file');
        // $fileName = $file->getClientOriginalName();
        // $path = $file->storeAs('csv_uploads', $fileName);
    
        // // Dispatch job to handle processing in background
        // ProcessCsvUpload::dispatch($path, $fileName);


        $file = $request->file('csv_file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Save to public/csv_uploads
        $path = $file->move(public_path('csv_uploads'), $fileName);

        // Dispatch job (pass full path to file and file name)
        ProcessCsvUpload::dispatch('csv_uploads/' . $fileName, $fileName);
    
        return response()->json([
            'message' => 'CSV upload started. Processing in background.',
        ]);
    }


}
