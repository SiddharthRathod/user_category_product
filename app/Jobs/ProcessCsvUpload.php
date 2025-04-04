<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\UploadLog;
use App\Events\UploadCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCsvUpload implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $path, public string $fileName) {}

    public function handle(): void
    {
        // $file = fopen(storage_path("app/{$this->path}"), 'r');
        // $header = fgetcsv($file);

        $fullPath = public_path($this->path);

        if (!file_exists($fullPath)) {
            \Log::error("File not found: " . $fullPath);
            return;
        }

        $file = fopen($fullPath, 'r');
        $header = fgetcsv($file);

        $inserted = $updated = $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            if (empty($data['name'])) {
                $skipped++;
                continue;
            }

            $contact = Contact::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'phone' => $data['phone']]
            );

            if ($contact->wasRecentlyCreated) {
                $inserted++;
            } else {
                $updated++;
            }
        }

        fclose($file);

        UploadLog::create([
            'file_name' => $this->fileName,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        broadcast(new UploadCompleted([
            'file' => $this->fileName,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ]));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->store('uploads');
        $filename = $request->file('csv_file')->getClientOriginalName();

        ProcessCsvUpload::dispatch($path, $filename);

        return response()->json(['status' => 'Processing started...']);
    }

}
