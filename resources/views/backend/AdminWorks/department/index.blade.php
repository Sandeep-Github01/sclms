@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Departments</h2>

    <a href="{{ route('admin.department.create') }}" class="btn-submit">Add Department</a>

    @if (session('success'))
        <p class="apply-success">{{ session('success') }}</p>
    @endif

    <div class="table-wrapper">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.department.edit', $department->id) }}"
                                    class="btn-table-view">Edit</a>
                                <form method="POST" action="{{ route('admin.department.destroy', $department->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-table-delete"
                                        onclick="return confirm('Delete this department?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if ($departments->isEmpty())
                    <tr>
                        <td colspan="3">No departments found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@include('backend.partials.footer')