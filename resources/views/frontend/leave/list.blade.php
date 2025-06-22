@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>My Leaves</h2>

    @if($leaves->isEmpty())
        <p>No leave records yet. <a href="{{ route('leave.create') }}">Apply now</a></p>
    @else
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $lv)
                <tr>
                    <td>{{ $lv->leaveType->name }}</td>
                    <td>{{ $lv->start_date }}</td>
                    <td>{{ $lv->end_date }}</td>
                    <td>{{ ucfirst($lv->status) }}</td>
                    <td><a href="{{ route('leave.show', $lv->id) }}">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@include('frontend.partials.footer')
