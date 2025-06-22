@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Leave Detail</h2>

    <p><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
    <p><strong>Dates:</strong> {{ $leave->start_date }} to {{ $leave->end_date }}</p>
    <p><strong>Status:</strong> {{ ucfirst($leave->status) }}</p>
    <p><strong>Review Type:</strong> {{ ucfirst($leave->review_type) }}</p>
    @if($leave->final_score !== null)
        <p><strong>Score:</strong> {{ $leave->final_score }}</p>
    @endif
    @if($leave->status_note)
        <p><strong>Note:</strong> {{ $leave->status_note }}</p>
    @endif
    @if($leave->file_path)
        <p><a href="{{ asset('storage/'.$leave->file_path) }}" target="_blank">View Document</a></p>
    @endif

    <p><a href="{{ route('leave.list') }}">Back to My Leaves</a></p>
</div>

@include('frontend.partials.footer')
