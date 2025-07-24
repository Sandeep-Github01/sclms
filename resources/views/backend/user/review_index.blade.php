@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Pending Profile Review Requests</h2>

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
                    @php
                        $studentRequests = $pendingRequests->where('user.role', 'student');
                    @endphp

                    @forelse($studentRequests as $request)
                        <tr>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->user->email }}</td>
                            <td>{{ $request->user->dept_name ?? 'N/A' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.user.profileReviewForm', $request->user->id) }}"
                                        class="btn-table-view">Review</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No pending student requests.</td>
                        </tr>
                    @endforelse
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
                    @php
                        $teacherRequests = $pendingRequests->where('user.role', 'teacher');
                    @endphp

                    @forelse($teacherRequests as $request)
                        <tr>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->user->email }}</td>
                            <td>{{ $request->user->dept_name ?? 'N/A' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.user.profileReviewForm', $request->user->id) }}"
                                        class="btn-table-view">Review</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No pending teacher requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.footer')
