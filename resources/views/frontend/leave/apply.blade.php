@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2 style="margin-top: 20px;">Apply for Leave</h2>

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

    <div class="apply-container">
        <div class="calendar-col">
            <div id="calendar"></div>
        </div>

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
        events: @json($blackouts),
        selectable: false,
        
        dayMaxEvents: 3,
        moreLinkText: 'more',
        
        dayCellDidMount: function(info) {
            const dayEl = info.el;
            const date = info.date;
            const today = new Date();
            
            if (date < today) {
                dayEl.classList.add('fc-day-past');
            }
            
            const blackoutEvents = @json($blackouts);
            const dayString = date.toISOString().split('T')[0];
            
            blackoutEvents.forEach(event => {
                const eventStart = new Date(event.start);
                const eventEnd = event.end ? new Date(event.end) : eventStart;
                
                if (date >= eventStart && date <= eventEnd) {
                    dayEl.classList.add('blackout-day');
                }
            });
            
            if (!dayEl.classList.contains('blackout-day')) {
                dayEl.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '10';
                    this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
                });
                
                dayEl.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = '1';
                    this.style.boxShadow = 'none';
                });
            }
        },
        
        eventDidMount: function(info) {
            const eventEl = info.el;
            
            if (info.event.title.toLowerCase().includes('blackout')) {
                eventEl.style.background = 'linear-gradient(135deg, #1a1a1a, #000000)';
                eventEl.style.border = '1px solid #333333';
                eventEl.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.6)';
                eventEl.style.textShadow = '0 1px 2px rgba(0, 0, 0, 0.8)';
            }
            else if (info.event.title.toLowerCase().includes('holiday')) {
                eventEl.style.background = 'linear-gradient(135deg, #ff6b6b, #ee5a52)';
                eventEl.style.boxShadow = '0 2px 8px rgba(255, 107, 107, 0.3)';
            }
            
            eventEl.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
                if (info.event.title.toLowerCase().includes('blackout')) {
                    this.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.8)';
                    this.style.background = 'linear-gradient(135deg, #000000, #1a1a1a)';
                } else if (info.event.title.toLowerCase().includes('holiday')) {
                    this.style.boxShadow = '0 6px 20px rgba(255, 107, 107, 0.4)';
                }
            });
            
            eventEl.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                if (info.event.title.toLowerCase().includes('blackout')) {
                    this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.6)';
                    this.style.background = 'linear-gradient(135deg, #1a1a1a, #000000)';
                } else if (info.event.title.toLowerCase().includes('holiday')) {
                    this.style.boxShadow = '0 2px 8px rgba(255, 107, 107, 0.3)';
                }
            });
        },
        
        loading: function(isLoading) {
            if (isLoading) {
                calendarEl.style.opacity = '0.6';
                calendarEl.style.pointerEvents = 'none';
            } else {
                calendarEl.style.opacity = '1';
                calendarEl.style.pointerEvents = 'auto';
            }
        },
        
        datesSet: function(info) {
            const dayGrid = calendarEl.querySelector('.fc-daygrid');
            if (dayGrid) {
                dayGrid.style.opacity = '0';
                dayGrid.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    dayGrid.style.transition = 'all 0.3s ease';
                    dayGrid.style.opacity = '1';
                    dayGrid.style.transform = 'translateY(0)';
                }, 50);
            }
        }
    });
    
    calendar.render();
    
    setTimeout(() => {
        calendarEl.style.opacity = '0';
        calendarEl.style.transform = 'translateY(30px)';
        calendarEl.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            calendarEl.style.opacity = '1';
            calendarEl.style.transform = 'translateY(0)';
        }, 100);
    }, 0);
});
</script>