@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Apply for Leave</h2>

    @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $msg => $color)
        @if(session($msg))
            <p style="color: {{ $color }};">{{ session($msg) }}</p>
        @endif
    @endforeach

    @if($errors->any())
        <div class="apply-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="apply-container">
        <div class="calendar-col">
            <div id="calendar"></div>
        </div>

        <div class="form-col">
            <form action="{{ route('leave.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="type_id">Leave Type:</label>
                    <select name="type_id" id="type_id" required>
                        <option value="">-- Select Leave Type --</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" required>
                </div>

                <div class="form-group">
                    <label for="reason">Reason:</label>
                    <textarea name="reason" id="reason"></textarea>
                </div>

                <div class="form-group">
                    <label for="document">Supporting Document (if any):</label>
                    <input type="file" name="document" id="document">
                </div>

                <button type="submit" class="btn-submit">
                    Submit Leave Request
                </button>
            </form>
        </div>
    </div>
</div>

@include('frontend.partials.footer')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const blackoutRanges = @json($blackouts);
        const blackoutDates = [];

        blackoutRanges.forEach(event => {
            let current = new Date(event.start);
            const end = new Date(event.end);

            current.setHours(0, 0, 0, 0);
            end.setHours(0, 0, 0, 0);

            while (current <= end) {
                const formatted = current.toISOString().split('T')[0];
                blackoutDates.push(formatted);
                current.setDate(current.getDate() + 1);
            }
        });

        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            height: 'auto',
            events: blackoutRanges,
            selectable: false,
            dayMaxEvents: 3,
            moreLinkText: 'more',

            dayCellDidMount: function (info) {
                const dateStr = info.date.toISOString().split('T')[0];
                if (blackoutDates.includes(dateStr)) {
                    info.el.classList.add('blackout-day');
                }
            }
        });

        calendar.render();
    });
</script>