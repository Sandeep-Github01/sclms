@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h2>Review Leave Application</h2>

        <div class="review-container">

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
                    <p><strong>Start Date:</strong> {{ $leave->start_date->format('Y-m-d') }}</p>
                    <p><strong>End Date:</strong> {{ $leave->end_date->format('Y-m-d') }}</p>
                    <p><strong>Duration:</strong> {{ $days }} day(s)</p>
                    <p><strong>Applied Date:</strong> {{ $leave->created_at->format('M d, Y g:i A') }}</p>
                    <p><strong>Review Type:</strong> Manual Review Required</p>
                </div>

                @if ($leave->reason)
                    <div class="detail-section">
                        <p><strong>Reason for Leave:</strong> {{ $leave->reason }}</p>
                    </div>
                @endif

                @if ($leave->file_path)
                    <br>
                    <hr><br>
                    <div class="detail-section">
                        <h3>Attached Document</h3>
                        <br>
                        <p>
                            <a href="{{ route('leave.document.download', $leave->id) }}" target="_blank"
                                class="btn-table-view" style="margin-left: 8px; width: 100px;">
                                View Document</a>
                        </p>
                    </div>
                @endif
            </div>
            <br>
            <hr><br>

            <div class="review-sidebar">

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

                <div class="info-card">
                    <h4>Recent Leave History (30 days)</h4>
                    @if ($recentLeaves->count() > 0)
                        <div class="recent-leaves">
                            @foreach ($recentLeaves as $recent)
                                <div class="recent-item">
                                    <p><strong>{{ $recent->leaveType->name }}</strong></p>
                                    <p class="recent-dates">{{ $recent->start_date->format('Y-m-d') }} to
                                        {{ $recent->end_date->format('Y-m-d') }}
                                    </p>
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


        <div class="decision-form">
            <h3>Admin Decision</h3>

            <form method="POST" action="{{ route('admin.process_decision', $leave->id) }}">
                @csrf

                <!-- Decision buttons -->
                <button type="submit" name="decision" value="approved" class="btn-table-view">Approve</button>
                <br>
                <br>
                <Strong>If Decline</strong>
                <label for="comment"><strong>Decline Reason:</strong></label>
                <textarea id="comment" name="comment" rows="4"
                    placeholder="Add a reason if you're rejecting..."></textarea>
                <br>
                <button type="submit" name="decision" value="rejected" class="btn-table-delete">Decline</button>
            </form>
            <br>

            <hr>

            <!-- SEPARATE abuse form -->
            <form action="{{ route('admin.leave.markAbuse', $leave->id) }}" method="POST"
                onsubmit="return confirm('Mark this leave as abuse? This will apply penalties.');">
                @csrf
                <input type="hidden" name="reason" value="admin_flagged_abuse">
                <label><strong>Admin Comment:</strong></label>
                <textarea name="comment" rows="3" placeholder="Detail the abuse here..."></textarea>
                <br>
                <button type="submit" class="btn-table-delete">Mark as Abuse</button>
            </form>

            <br><hr>
            <a href="{{ route('admin.leaves.index') }}" class="btn-back">Back</a>
        </div>

    </div>
</div>

@include('backend.partials.footer')