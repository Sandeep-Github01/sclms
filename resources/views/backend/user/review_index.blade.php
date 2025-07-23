@include('backend.partials.header')
@include('backend.partials.sidebar')
{{-- @dd('xa'); --}}
<div class="main-content">
    <h2>Pending Profile Review Requests</h2>

    <div class="user-section">
        <h3 class="section-title">Pending Requests</h3>

        <div class="table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $request)
                        <tr>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->user->email }}</td>
                            <td>{{ ucfirst($request->user->role) }}</td>
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
                            <td colspan="5">No pending profile requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.footer')
