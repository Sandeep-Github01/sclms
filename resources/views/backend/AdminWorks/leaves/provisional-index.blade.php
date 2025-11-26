@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Provisional Leaves - Awaiting Document Review</h2>

    <div class="table-wrapper">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Leave ID</th>
                    <th>Applicant</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Uploaded At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->id }}</td>
                        <td>{{ $leave->user->name }}</td>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                        <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                        <td>{{ $leave->updated_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.leaves.show', $leave->id) }}" class="btn-table-view">Review</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No provisional leaves with submitted documents.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('backend.partials.footer')