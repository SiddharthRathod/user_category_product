@extends('layouts.webapp')

@section('content')
<div class="container mt-4">
    
    <h2 class="mb-4">Upload</h2>

    <div class="row mb-3">
        <div class="col-md-4">
            <form id="upload-form" enctype="multipart/form-data" mehod="POST" action="javascript:void(0);">
                <input type="file" name="csv_file" id="csv_file" required>
                <button type="submit">Upload</button>
            </form>
        </div>        
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div id="result"></div>
        </div>        
    </div>

    
</div>
@push('script')
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    

    <script>
        $(document).ready(function() {
            $('#upload-form').submit(function(e) {

                e.preventDefault();

                let fileInput = $('#csv_file')[0];
                let file = fileInput.files[0];

                if (!file || !file.name.endsWith('.csv')) {
                    alert('Please upload a valid CSV file.');
                    return;
                }

                let formData = new FormData();
                formData.append('csv_file', file);

                $.ajax({
                    url: "{{ route('contacts.csv-upload') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        $('#result').html(`
                            <p><strong>Inserted:</strong> ${data.inserted}</p>
                            <p><strong>Updated:</strong> ${data.updated}</p>
                            <p><strong>Skipped:</strong> ${data.skipped}</p>
                        `);
                    }
                });
            });

        });

        const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            encrypted: true
        });

        const channel = pusher.subscribe('csv-upload');
        channel.bind('App\\Events\\UploadCompleted', function(data) {
            $('#result').html(`
                <p><strong>File:</strong> ${data.file}</p>
                <p><strong>Inserted:</strong> ${data.inserted}</p>
                <p><strong>Updated:</strong> ${data.updated}</p>
                <p><strong>Skipped:</strong> ${data.skipped}</p>
            `);
        });
    </script>
@endpush
@endsection