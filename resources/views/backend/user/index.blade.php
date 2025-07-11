@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>All Users (Admin View Only)</h2>

    {{-- Students Group --}}
    <h3>Students</h3>
    @foreach($students->groupBy('department.name') as $department => $grouped)
        <h4>Department: {{ $department }}</h4>
        <ul>
            @foreach($grouped as $student)
                <li>{{ $student->name }} ({{ $student->email }})</li>
            @endforeach
        </ul>
    @endforeach

    {{-- Teachers Group --}}
    <h3>Teachers</h3>
    @foreach($teachers->groupBy('department.name') as $department => $grouped)
        <h4>Department: {{ $department }}</h4>
        <ul>
            @foreach($grouped as $teacher)
                <li>{{ $teacher->name }} ({{ $teacher->email }})</li>
            @endforeach
        </ul>
    @endforeach
</div>

@include('backend.partials.footer')
