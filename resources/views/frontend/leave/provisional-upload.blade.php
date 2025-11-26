@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Upload Document – Leave #{{ $leave->id }}</h2>

    @if(session('error'))
        <p style="color:red;">{{ session('error') }}</p>
    @endif

    <div class="user-detail-card">
        <div class="detail-section">
            <p><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</p>
            <p><strong>Start Date:</strong> {{ $leave->start_date->format('Y-m-d') }}</p>
            <p><strong>End Date:</strong> {{ $leave->end_date->format('Y-m-d') }}</p>
            <p><strong>Upload Deadline:</strong> {{ $leave->document_deadline->format('Y-m-d') }}</p>
        </div>
    </div>

    <form action="{{ route('leave.provisional.upload.store', $leave->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="document">Choose Document (PDF / Image)</label>
            <input type="file" name="document" id="document" required>
        </div>
        <button type="submit" class="btn-submit">Upload</button>
        <a href="{{ route('leave.provisional.index') }}" class="btn-back">Cancel</a>
    </form>
</div>

@include('frontend.partials.footer')