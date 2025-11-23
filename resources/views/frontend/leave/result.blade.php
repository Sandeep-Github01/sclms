@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Leave Request Result</h2>

    <p style="margin: 4px 0"><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
    <p style="margin: 4px 0">
        <strong>Dates:</strong>
        {{ \Carbon\Carbon::parse($leave->start_date->format('Y-m-d')) }} to
        {{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}
    </p>
    <p style="margin: 4px 0"><strong>Duration:</strong>
        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 }} day(s)
    </p>
    <p style="margin: 4px 0"><strong>Status:</strong>
        <span
            class="text-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
            {{ ucfirst($leave->status) }}
        </span>
    </p>
    <p style="margin: 4px 0"><strong>Review Type:</strong> {{ ucfirst($leave->review_type) }}</p>
    @if($leave->final_score !== null)
        <p style="margin: 4px 0"><strong>Score:</strong> {{ $leave->final_score }}/10</p>
    @endif
    @if($leave->status_note)
        <p style="margin: 4px 0"><strong>Note:</strong> {{ $leave->status_note }}</p>
    @endif
    @if($leave->file_path)
        <p style="margin: 10px 0 4px 0;">
            <strong>Document:</strong>
            <a href="{{ route('leave.document.download', $leave->id) }}" target="_blank" class="btn-table-view"
                style="margin-left: 8px; width: 100px;">
                View Document
            </a>
        </p>
    @endif

    <p><a href="{{ route('leave.list') }}" class="btn-back">Back</a></p>
</div>

@include('frontend.partials.footer')