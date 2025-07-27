@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
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
                <br>
                <hr><br>
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
                    <br>
                    <hr><br>
                    <div class="detail-section">
                        <h3>Attached Document</h3>
                        <p><a href="{{ asset('storage/' . $leave->file_path) }}" target="_blank"
                                class="btn-document">View Document</a>
                        </p>
                    </div>
                @endif
            </div>
            <br>
            <hr><br>
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
                <br>
                <hr><br>
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
        <br>
        <hr><br>
        {{-- Decision Form --}}
        <div class="decision-form">
            <h3>Admin Decision</h3>

            <form method="POST" action="{{ route('admin.process_decision', $leave->id) }}">
                @csrf

                {{-- Approve Button --}}
                <button type="submit" name="decision" value="approved" class="btn-table-view">
                    Approve
                </button>

                <br><br>
                <hr><br>

                {{-- Decline Reason Textarea --}}
                <label for="comment"><strong>Decline Reason (if applicable):</strong></label><br>
                <textarea id="comment" name="comment" rows="4" style="width: 100%; margin-bottom: 15px;"
                    placeholder="Add a reason if you're rejecting..."></textarea>

                {{-- Decline Button --}}
                <button type="submit" name="decision" value="rejected" class="btn-table-delete">
                    Decline
                </button>
            </form>

            <a href="{{ route('admin.leaves.index') }}" class="btn-back">Back</a>
        </div>

    </div>
</div>

@include('backend.partials.footer')
