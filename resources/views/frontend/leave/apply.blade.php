{{-- resources/views/frontend/leave/apply.blade.php --}}
@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2 style="margin-top: 20px;">Apply for Leave</h2>

    {{-- Flash messages --}}
    @foreach (['success'=>'green','error'=>'red','info'=>'blue'] as $msg=>$color)
        @if(session($msg))
            <p style="color: {{ $color }};">{{ session($msg) }}</p>
        @endif
    @endforeach

    @if($errors->any())
        <div style="color:red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Responsive container: mobile maa stacked, desktop maa two columns --}}
    <div class="apply-container">
        {{-- Calendar Column --}}
        <div class="calendar-col">
            <h3 style="margin-bottom: 10px;">📅 Blackout & Sundays</h3>
            <div id="calendar"></div>
        </div>

        {{-- Form Column --}}
        <div class="form-col">
            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label>Leave Type:</label><br>
                <select name="type_id" required style="width:100%; padding:8px;">
                    <option value="">-- Select Leave Type --</option>
                    @foreach($leaveTypes as $lt)
                        <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                    @endforeach
                </select><br><br>

                <label>Start Date:</label><br>
                <input type="date" name="start_date" required style="width:100%; padding:8px;"><br><br>

                <label>End Date:</label><br>
                <input type="date" name="end_date" required style="width:100%; padding:8px;"><br><br>

                <label>Reason:</label><br>
                <textarea name="reason" style="width:100%; height:100px; padding:8px;"></textarea><br><br>

                <label>Supporting Document (if any):</label><br>
                <input type="file" name="document"><br><br>

                <button type="submit" style="padding:10px 20px; background:#0d47a1; color:white; border:none; border-radius:4px;">
                    Submit Leave Request
                </button>
            </form>
        </div>
    </div>
</div>

@include('frontend.partials.footer')

{{-- FullCalendar initialization --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        height: 'auto',
        events: @json($blackouts), // controller bata pass bhayeko blackout periods
        selectable: false,
        // Sundays highlight via CSS (.fc-daygrid-day.fc-day-sun), so no extra JS needed
        // Blackout background handled by events with display:'background' and color:'black' or rgba
    });
    calendar.render();
});
</script>
