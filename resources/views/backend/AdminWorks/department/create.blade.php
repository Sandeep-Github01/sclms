@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Create Department</h2>

    @if ($errors->any())
        <div class="apply-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.department.store') }}" method="POST">
        @csrf

        <label for="name">Department Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <button type="submit" class="btn-submit">Save</button>
    </form>
</div>

@include('backend.partials.footer')
