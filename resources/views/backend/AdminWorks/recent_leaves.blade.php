@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Recent Leave Requests</h2>

    {{-- Students Section --}}
    <div class="user-section">
        <h3 class="section-title">Students</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date }}</td>
                            <td>{{ $leave->end_date }}</td>
                            <td>{{ ucfirst($leave->status) }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.review_leave', ['id' => $leave->id]) }}" class="btn-table-view">Review</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No leave requests found for students.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teachers Section --}}
    <div class="user-section">
        <h3 class="section-title">Teachers</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date }}</td>
                            <td>{{ $leave->end_date }}</td>
                            <td>{{ ucfirst($leave->status) }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.review_leave', ['id' => $leave->id]) }}" class="btn-table-view">Review</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No leave requests found for teachers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.footer')
