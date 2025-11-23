@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Edit Blackout Period</h2>

    @if ($errors->any())
        <div class="apply-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.blackout.update', $blackout->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date', $blackout->start_date->format('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" value="{{ old('end_date', $blackout->end_date->format('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
            <label for="reason">Reason</label>
            <input type="text" name="reason" value="{{ old('reason', $blackout->reason) }}">
        </div>

        <div class="form-group">
            <label for="department_id">Departments</label>
            <select name="department_id[]" id="department_id" class="select2" multiple="multiple" style="width:100%;">
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ in_array($dept->id, old('department_id', $blackout->department_id ?? [])) ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="semester">Semesters</label>
            <select name="semester[]" id="semester" class="select2" multiple="multiple" style="width:100%;">
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ in_array($i, old('semester', $blackout->semester ?? [])) ? 'selected' : '' }}>
                        Semester {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        <button type="submit" class="btn-submit">Update</button>
    </form>
</div>

@include('backend.partials.footer')