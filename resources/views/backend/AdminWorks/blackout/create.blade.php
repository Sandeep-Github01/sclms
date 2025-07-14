@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Create Blackout Period</h2>

    @if ($errors->any())
        <div class="apply-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.blackout.store') }}" method="POST">
        @csrf

        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" value="{{ old('start_date') }}" required>

        <label for="end_date">End Date</label>
        <input type="date" name="end_date" value="{{ old('end_date') }}" required>

        <label for="reason">Reason</label>
        <input type="text" name="reason" value="{{ old('reason') }}">

        <button type="submit" class="btn-submit">Save</button>
    </form>
</div>

@include('backend.partials.footer')
