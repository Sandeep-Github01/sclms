@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>All Users <span class="text-muted">(Admin View Only)</span></h2>

    {{-- Students Section --}}
    <div class="user-section">
        <h3 class="section-title">Students</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->department->name ?? 'N/A' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.user.show', $student->id) }}" class="btn-table-view">View</a>
                                    <form action="{{ route('admin.user.destroy', $student->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-table-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teachers Section --}}
    <div class="user-section">
        <h3 class="section-title">Teachers</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                        <tr>
                            <td>{{ $teacher->name }}</td>
                            <td>{{ $teacher->email }}</td>
                            <td>{{ $teacher->department->name ?? 'N/A' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.user.show', $teacher->id) }}" class="btn-table-view">View</a>
                                    <form action="{{ route('admin.user.destroy', $teacher->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-table-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.footer')