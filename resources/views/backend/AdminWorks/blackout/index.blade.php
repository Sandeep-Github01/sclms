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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blackouts as $blackout)
                    <tr>
                        <td>{{ $blackout->id }}</td>
                        <td>{{ $blackout->start_date }}</td>
                        <td>{{ $blackout->end_date }}</td>
                        <td>{{ $blackout->reason }}</td>
                        <td class="table-actions">
                            <a href="{{ route('admin.blackout.edit', $blackout->id) }}" class="btn-table-view">Edit</a>
                            <form method="POST" action="{{ route('admin.blackout.destroy', $blackout->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-table-delete" onclick="return confirm('Delete this blackout period?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if ($blackouts->isEmpty())
                    <tr>
                        <td colspan="5">No blackout periods found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@include('backend.partials.footer')
