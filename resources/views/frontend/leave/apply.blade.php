{{-- @include('frontend.partials.header')
@include('frontend.partials.sidebar') --}}

{{-- <div style="margin-left: 230px; padding: 20px;"> --}}
{{-- <div class="main-content">
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

        <label>Reason:</label><br>
        <textarea name="reason"></textarea><br><br>

        <label>Supporting Document (if any):</label><br>
        <input type="file" name="document"><br><br>

        <button type="submit">Submit Leave Request</button>
    </form>
</div>

@include('frontend.partials.footer') --}}


@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2 style="margin-top: 20px;">Apply for Leave</h2>

    {{-- Flash Messages --}}
    @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $msg => $color)
        @if(session($msg))
            <p style="color: {{ $color }};">{{ session($msg) }}</p>
        @endif
    @endforeach

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Calendar Preview --}}
    <div style="margin: 40px auto; max-width: 1000px;">
        <h3>📅 Blackout Periods (Exam Days, Restricted Dates)</h3>
        <div id="calendar" style="border: 1px solid #ccc; border-radius: 6px; padding: 10px;"></div>
    </div>

    {{-- Leave Application Form --}}
    <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data" style="max-width: 700px; margin: auto;">
        @csrf

        <label>Leave Type:</label><br>
        <select name="type_id" required style="width: 100%; padding: 8px;">
            <option value="">-- Select Leave Type --</option>
            @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
            @endforeach
        </select><br><br>

        <label>Start Date:</label><br>
        <input type="date" name="start_date" required style="width: 100%; padding: 8px;"><br><br>

        <label>End Date:</label><br>
        <input type="date" name="end_date" required style="width: 100%; padding: 8px;"><br><br>

        <label>Reason:</label><br>
        <textarea name="reason" style="width: 100%; height: 100px; padding: 8px;"></textarea><br><br>

        <label>Supporting Document (if any):</label><br>
        <input type="file" name="document"><br><br>

        <button type="submit" style="padding: 10px 20px; background: #0d47a1; color: white; border: none; border-radius: 4px;">
            Submit Leave Request
        </button>
    </form>
</div>

@include('frontend.partials.footer')

{{-- FullCalendar Setup --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 450,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            events: @json($blackouts), // [{ start, end, title, color }]
            selectable: false,
            eventDisplay: 'background'
        });
        calendar.render();
    });
</script>
