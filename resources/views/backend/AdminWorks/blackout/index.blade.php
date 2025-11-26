@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Blackout Periods</h2>

    <a href="{{ route('admin.blackout.create') }}" class="btn-submit">Add Blackout Period</a>

    @if (session('success'))
        <p class="apply-success">{{ session('success') }}</p>
    @endif

    <div class="table-wrapper">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Departments</th>
                    <th>Semesters</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blackouts as $blackout)
                    <tr>
                        <td>{{ $blackout->id }}</td>
                        <td>{{ $blackout->start_date }}</td>
                        <td>{{ $blackout->end_date }}</td>
                        <td>{{ $blackout->reason }}</td>
                        <td>
                            @if(!empty($blackout->department_id))
                                @foreach($blackout->department_id as $deptId)
                                    {{ \App\Models\Department::find($deptId)->name ?? 'Unknown' }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @else
                                All Departments
                            @endif
                        </td>
                        <td>
                            {{ !empty($blackout->semester) ? implode(', ', $blackout->semester) : 'All Semesters' }}
                        </td>
                        <td class="table-actions">
                            <a href="{{ route('admin.blackout.edit', $blackout->id) }}" class="btn-table-view">Edit</a>
                            <form method="POST" action="{{ route('admin.blackout.destroy', $blackout->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-table-delete"
                                    onclick="return confirm('Delete this blackout period?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No blackout periods found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('backend.partials.footer')