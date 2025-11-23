@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>My Leaves</h2>

    @if ($leaves->isEmpty())
        <p>No leave records yet. <a href="{{ route('leave.create') }}">Apply now</a></p>
    @else
        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Applied Date</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaves as $lv)
                        <tr>
                            <td>{{ $lv->created_at }}</td>
                            <td>{{ $lv->leaveType->name }}</td>
                            <td>{{ $lv->start_date->format('Y-m-d') }}</td>
                            <td>{{ $lv->end_date->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($lv->status) }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('leave.show', $lv->id) }}" class="btn-table-view">View</a>
                                    @if ($lv->status === 'pending')
                                        <form action="{{ route('leave.cancel', $lv->id) }}" method="POST"
                                              onsubmit="return confirm('Cancel this leave request?');">
                                            @csrf
                                            {{-- <button type="submit" class="btn-cancel">Cancel</button> --}}
                                            <button type="submit" class="btn-table-delete">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@include('frontend.partials.footer')