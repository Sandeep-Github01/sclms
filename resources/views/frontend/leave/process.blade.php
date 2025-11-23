{{-- @include('frontend.partials.header')
@include('frontend.partials.sidebar')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="main-content">
    <h2>Leave Request Evaluation Steps</h2>

    <div class="review-log">
        <h3>Evaluation Log:</h3>
        <ol id="logList"></ol>
        <p id="logEmpty" style="display: none;">No log available.</p>

        <hr>

        <div id="resultSummary" style="opacity: 0; transition: opacity 0.5s;">
            <h3>Result Summary</h3>
            <p><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
            <p><strong>Dates:</strong> {{ $leave->start_date }} to {{ $leave->end_date }}</p>
            <p><strong>Status:</strong> {{ ucfirst($leave->status) }}</p>
            <p><strong>Review Type:</strong> {{ ucfirst($leave->review_type) }}</p>
            @if ($leave->final_score !== null)
                <p><strong>Score:</strong> {{ $leave->final_score }}</p>
            @endif
            @if ($leave->status_note)
                <p><strong>Note:</strong> {{ $leave->status_note }}</p>
            @endif
            @if ($leave->file_path)
                <p><a href="{{ route('leave.document', $leave) }}" target="_blank">View Document</a>
                </p>
            @endif

            <a href="{{ route('leave.result', $leave->id) }}" class="btn-submit">
                View Final Result
            </a>
        </div>
    </div>
</div>

@include('frontend.partials.footer')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const steps = @json($steps);
        const logList = document.getElementById('logList');
        const logEmpty = document.getElementById('logEmpty');
        const resultSummary = document.getElementById('resultSummary');

        if (!steps || steps.length === 0) {
            logEmpty.style.display = 'block';
            return;
        }

        let i = 0;
        const interval = setInterval(() => {
            if (i < steps.length) {
                const step = steps[i];

                const li = document.createElement('li');
                li.classList.add('log-item', `log-${step.type}`);

                const icon = document.createElement('span');
                icon.classList.add('log-icon');

                if (step.type === 'success') {
                    icon.innerHTML =
                    '<i class="fa fa-check-circle fa-spin" style="color:#4caf50;"></i>';
                } else if (step.type === 'warning') {
                    icon.innerHTML =
                        '<i class="fa fa-exclamation-triangle fa-bounce" style="color:#ff9800;"></i>';
                } else if (step.type === 'error') {
                    icon.innerHTML =
                        '<i class="fa fa-times-circle fa-shake" style="color:#f44336;"></i>';
                } else if (step.type === 'document') {
                    icon.innerHTML = '<i class="fa fa-file-alt fa-beat" style="color:#3f51b5;"></i>';
                } else {
                    icon.innerHTML = '•';
                }

                const textSpan = document.createElement('span');
                textSpan.classList.add('log-text');
                textSpan.textContent = `${step.text} (Score: ${step.score})`;

                li.appendChild(icon);
                li.appendChild(textSpan);

                li.style.opacity = 0;
                li.style.transition = 'opacity 0.5s';
                logList.appendChild(li);
                requestAnimationFrame(() => li.style.opacity = 1);

                i++;
            } else {
                clearInterval(interval);
                resultSummary.style.opacity = 1;
            }
        }, 800);
    });
</script> --}}

@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="main-content">
    <h2>Leave Request Evaluation Steps</h2>

    {{-- NEW: probability + summary (same styling) ----------------------------}}
    <div class="review-log" style="margin-bottom: 25px;">
        <!-- Probability -->
        <div class="card mb-3">
            <div class="card-body" style="padding: 15px 20px;">
                <h5 style="margin: 0;font-size: 1.1em">Approval Probability: {{ $probability }}%
                    <span class="badge" style="background: {{ $probLabel == 'High' ? '#4caf50' : ($probLabel == 'Moderate' ? '#ff9800' : '#f44336') }};
                                     color:#fff; padding:4px 8px; border-radius:3px; font-size:0.9em;">
                        {{ $probLabel }}
                    </span>
                </h5>
                <p class="text-muted" style="margin:5px 0 0 0;font-size:0.95em">
                    @if($probLabel == 'High') Your request looks safe - high chance of auto-approval.
                    @elseif($probLabel == 'Moderate') Small issues detected
                    @else Several risk flags found - usually auto-rejected.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="review-log">
        <h3>Evaluation Log:</h3>
        <ol id="logList"></ol>
        <p id="logEmpty" style="display: none;">No log available.</p>

        <hr>

        <div id="resultSummary" style="opacity: 0; transition: opacity 0.5s;">
            <h3>Result Summary</h3>
            <p style="margin: 4px 0"><strong>Type:</strong> {{ $leave->leaveType->name }}</p>
            <p style="margin: 4px 0">
                <strong>Dates:</strong>
                {{ \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }} to
                {{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}
            </p>
            <p style="margin: 4px 0"><strong>Duration:</strong>
                {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 }} day(s)
            </p>
            <p style="margin: 4px 0"><strong>Status:</strong>
                <span
                    class="text-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($leave->status) }}
                </span>
            </p>
            <p style="margin: 4px 0"><strong>Review Type:</strong> {{ ucfirst($leave->review_type) }}</p>
            @if($leave->final_score !== null)
                <p style="margin: 4px 0"><strong>Score:</strong> {{ $score }}/10</p>
            @endif
            @if($leave->status_note)
                <p style="margin: 4px 0"><strong>Note:</strong> {{ $leave->status_note }}</p>
            @endif
            @if($leave->file_path)
                <p style="margin: 10px 0 4px 0;">
                    <strong>Document:</strong>
                    <a href="{{ route('leave.document.download', $leave->id) }}" target="_blank" class="btn-table-view"
                        style="margin-left: 8px; width: 100px;">
                        View Document
                    </a>
                </p>
            @endif
            <a href="{{ route('leave.list') }}" class="btn-back">Back</a>
        </div>
    </div>
</div>

@include('frontend.partials.footer')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const steps = @json($steps);
        const logList = document.getElementById('logList');
        const logEmpty = document.getElementById('logEmpty');
        const resultSummary = document.getElementById('resultSummary');

        if (!steps || steps.length === 0) {
            logEmpty.style.display = 'block';
            return;
        }

        let i = 0;
        const interval = setInterval(() => {
            if (i < steps.length) {
                const step = steps[i];

                const li = document.createElement('li');
                li.classList.add('log-item', `log-${step.type}`);

                const icon = document.createElement('span');
                icon.classList.add('log-icon');

                if (step.type === 'success') {
                    icon.innerHTML =
                        '<i class="fa fa-check-circle fa-spin" style="color:#4caf50;"></i>';
                } else if (step.type === 'warning') {
                    icon.innerHTML =
                        '<i class="fa fa-exclamation-triangle fa-bounce" style="color:#ff9800;"></i>';
                } else if (step.type === 'error') {
                    icon.innerHTML =
                        '<i class="fa fa-times-circle fa-shake" style="color:#f44336;"></i>';
                } else if (step.type === 'document') {
                    icon.innerHTML = '<i class="fa fa-file-alt fa-beat" style="color:#3f51b5;"></i>';
                } else {
                    icon.innerHTML = '•';
                }

                const textSpan = document.createElement('span');
                textSpan.classList.add('log-text');
                textSpan.textContent = `${step.text} (Score: ${step.score})`;

                li.appendChild(icon);
                li.appendChild(textSpan);

                li.style.opacity = 0;
                li.style.transition = 'opacity 0.5s';
                logList.appendChild(li);
                requestAnimationFrame(() => li.style.opacity = 1);

                i++;
            } else {
                clearInterval(interval);
                resultSummary.style.opacity = 1;
            }
        }, 800);
    });
</script>