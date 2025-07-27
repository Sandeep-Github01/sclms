@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Review Leave Application</h2>

    <div class="review-container">
        {{-- Leave Details Section --}}
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

            <div class="detail-section">
                <h3>Leave Information</h3>
                <p><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
                <p><strong>Start Date:</strong> {{ $leave->start_date }}</p>
                <p><strong>End Date:</strong> {{ $leave->end_date }}</p>
                <p><strong>Duration:</strong> {{ $days }} day(s)</p>
                <p><strong>Applied Date:</strong> {{ $leave->created_at->format('M d, Y g:i A') }}</p>
                <p><strong>Review Type:</strong> Manual Review Required</p>
            </div>

            @if ($leave->reason)
                <div class="detail-section">
                    <h3>Reason for Leave</h3>
                    <div class="reason-box">
                        {{ $leave->reason }}
                    </div>
                </div>
            @endif

            @if ($leave->file_path)
                <div class="detail-section">
                    <h3>Attached Document</h3>
                    <p><a href="{{ asset('storage/' . $leave->file_path) }}" target="_blank" class="btn-document">View
                            Document</a></p>
                </div>
            @endif
        </div>

        {{-- Review Info Sidebar --}}
        <div class="review-sidebar">
            {{-- Leave Credit Status --}}
            <div class="info-card">
                <h4>Leave Credit Status</h4>
                @if ($leaveCredit)
                    <div class="credit-info">
                        <p><strong>Available Credits:</strong> {{ $leaveCredit->remaining_days }} days</p>
                        <p><strong>Required:</strong> {{ $days }} days</p>
                        @if ($leaveCredit->remaining_days >= $days)
                            <p class="credit-status sufficient">✓ Sufficient Credits</p>
                        @else
                            <p class="credit-status insufficient">⚠ Insufficient Credits</p>
                        @endif
                    </div>
                @else
                    <p class="no-credit">No leave credit record found</p>
                @endif
            </div>

            {{-- Recent Leave History --}}
            <div class="info-card">
                <h4>Recent Leave History (30 days)</h4>
                @if ($recentLeaves->count() > 0)
                    <div class="recent-leaves">
                        @foreach ($recentLeaves as $recent)
                            <div class="recent-item">
                                <p><strong>{{ $recent->leaveType->name }}</strong></p>
                                <p class="recent-dates">{{ $recent->start_date }} to {{ $recent->end_date }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="no-recent">No recent leaves taken</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Decision Form --}}
    <div class="decision-form">
        <h3>Admin Decision</h3>
        <form method="POST" action="{{ route('admin.process_decision', $leave->id) }}">
            @csrf

            <div class="decision-options">
                <div class="radio-group">
                    <label class="radio-option approve-option">
                        <input type="radio" name="decision" value="approved" required>
                        <span class="radio-label">✓ Approve</span>
                    </label>

                    <label class="radio-option reject-option">
                        <input type="radio" name="decision" value="rejected" required>
                        <span class="radio-label">✗ Reject</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="comment">Comment (Optional)</label>
                <textarea id="comment" name="comment" rows="4" placeholder="Add your comment or reason for the decision..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Submit Decision</button>
                <a href="{{ route('admin.leaves.index') }}" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const decision = document.querySelector('input[name="decision"]:checked');
            if (!decision) {
                e.preventDefault();
                alert('Please select a decision (Approve or Reject)');
                return;
            }

            const confirmMsg = decision.value === 'approved' ?
                'Are you sure you want to approve this leave application?' :
                'Are you sure you want to reject this leave application?';

            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });
</script>

@include('backend.partials.footer')