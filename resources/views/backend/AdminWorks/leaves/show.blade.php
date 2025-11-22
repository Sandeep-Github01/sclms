@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h2>Leave Request Details</h2>

        <div class="leave-details">
            <div class="detail-section">
                <h3>Applicant Information</h3>
                <p><strong>Name:</strong> {{ $leave->user->name }}</p>
                <p><strong>Role:</strong> {{ ucfirst($leave->user->role) }}</p>
                <p><strong>Department:</strong> {{ $leave->department->name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $leave->user->email }}</p>
                @if ($leave->user->role === 'student' && $leave->user->semester)
                    <p><strong>Semester:</strong> {{ $leave->user->semester }}</p>
                @endif
            </div>
            <br>
            <hr><br>
            <div class="detail-section">
                <h3>Leave Information</h3>
                <p><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
                <p><strong>Start Date:</strong> {{ $leave->start_date }}</p>
                <p><strong>End Date:</strong> {{ $leave->end_date }}</p>
                <p><strong>Duration:</strong>
                    {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}
                    day(s)</p>
                <p><strong>Status:</strong>
                    <span class="status-badge status-{{ $leave->status }}">
                        {{ ucfirst($leave->status) }}
                    </span>
                </p>
                <p><strong>Review Type:</strong> {{ ucfirst($leave->review_type) }}</p>
                @if ($leave->final_score !== null)
                    <p><strong>Score:</strong> {{ $leave->final_score }}</p>
                @endif
            </div>

            @if ($leave->reason)
                <div class="detail-section">
                    <h3>Reason</h3>
                    <p>{{ $leave->reason }}</p>
                </div>
            @endif

            @if ($leave->status_note)
                <div class="detail-section">
                    <h3>Status Note</h3>
                    <p>{{ $leave->status_note }}</p>
                </div>
            @endif

            @if ($leave->file_path)
                <div class="detail-section">
                    <h3>Attached Document</h3>
                    <p><a href="{{ route('leave.document', $leave) }}" target="_blank">View Document</a>
                    </p>
                </div>
            @endif

            @if ($leave->approval)
                <div class="detail-section">
                    <h3>Admin Decision</h3>
                    <p><strong>Reviewed By:</strong> {{ $leave->approval->approver->name ?? 'N/A' }}</p>
                    <p><strong>Decision:</strong>
                        <span class="status-badge status-{{ $leave->approval->status }}">
                            {{ ucfirst($leave->approval->status) }}
                        </span>
                    </p>
                    @if ($leave->approval->comment)
                        <p><strong>Admin Comment:</strong> {{ $leave->approval->comment }}</p>
                    @endif
                    <p><strong>Reviewed At:</strong> {{ $leave->approval->created_at->format('M d, Y g:i A') }}</p>
                </div>
            @endif
            <br>
            <hr><br>
            <div class="detail-section">
                <h3>Application Details</h3>
                <p><strong>Applied At:</strong> {{ $leave->created_at->format('M d, Y g:i A') }}</p>
                <p><strong>Last Updated:</strong> {{ $leave->updated_at->format('M d, Y g:i A') }}</p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('admin.leaves.recent') }}" class="btn-back">Back</a>
            @if ($leave->status === 'pending' && $leave->review_type === 'manual')
                <a href="{{ route('admin.review_leave', ['id' => $leave->id]) }}" class="btn-primary">Review
                    Application</a>
            @endif
        </div>
    </div>
</div>

@include('backend.partials.footer')
