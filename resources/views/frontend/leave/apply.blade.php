@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>Apply for Leave</h2>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif
    @if(session('info'))
        <p style="color: blue;">{{ session('info') }}</p>
    @endif

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Leave Type:</label><br>
        <select name="type_id" required>
            <option value="">Select</option>
            @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
            @endforeach
        </select><br><br>

        <label>Start Date:</label><br>
        <input type="date" name="start_date" required><br><br>

        <label>End Date:</label><br>
        <input type="date" name="end_date" required><br><br>

        <label>Reason (optional):</label><br>
        <textarea name="reason"></textarea><br><br>

        <label>Supporting Document (if any):</label><br>
        <input type="file" name="document"><br><br>

        <button type="submit">Submit Leave Request</button>
    </form>
</div>

@include('frontend.partials.footer')
