@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Leave Applications - Manual Review Required</h2>

    {{-- Students Section --}}
    <div class="user-section">
        <h3 class="section-title">Students</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->department->name ?? 'N/A' }}</td>
                            <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date }}</td>
                            <td>{{ $leave->end_date }}</td>
                            <td>{{ $leave->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.review_leave', ['id' => $leave->id]) }}"
                                        class="btn-table-review">Review</a>
                                    <a href="{{ route('admin.leaves.show', ['id' => $leave->id]) }}"
                                        class="btn-table-view">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No pending leave requests from students.</td>
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
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $leave)
                        <tr>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->department->name ?? 'N/A' }}</td>
                            <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                            <td>{{ $leave->start_date }}</td>
                            <td>{{ $leave->end_date }}</td>
                            <td>{{ $leave->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.review_leave', ['id' => $leave->id]) }}"
                                        class="btn-table-review">Review</a>
                                    <a href="{{ route('admin.leaves.show', ['id' => $leave->id]) }}"
                                        class="btn-table-view">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No pending leave requests from teachers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.footer')
