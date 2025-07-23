@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h3 class="section-title">Review Profile Update</h3>

        <div class="user-detail-image-section">
            @if ($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" class="profile-image">
            @else
                <div class="user-detail-placeholder">No Image</div>
            @endif
        </div>

        <br>
        <hr><br>

        @php
            $requested = $profileRequest->data ?? [];
        @endphp

        <ul class="user-detail-list">
            <li>
                <strong>Name:</strong> {{ $user->name }}
                @if (isset($requested['name']) && $requested['name'] !== $user->name)
                    <br><span class="text-warning">Requested change: {{ $requested['name'] }}</span>
                @endif
            </li>

            <li>
                <strong>Email:</strong> {{ $user->email }}
                @if (isset($requested['email']) && $requested['email'] !== $user->email)
                    <br><span class="text-warning">Requested change: {{ $requested['email'] }}</span>
                @endif
            </li>

            <li>
                <strong>Role:</strong> {{ ucfirst($user->role) }}
                @if (isset($requested['role']) && $requested['role'] !== $user->role)
                    <br><span class="text-warning">Requested change: {{ ucfirst($requested['role']) }}</span>
                @endif
            </li>

            <li>
                <strong>Department:</strong> {{ $user->department->name ?? ($user->dept_name ?? 'N/A') }}
                @if (isset($requested['dept_name']) && $requested['dept_name'] !== $user->dept_name)
                    <br><span class="text-warning">Requested change: {{ $requested['dept_name'] }}</span>
                @endif
            </li>

            <li>
                <strong>DOB:</strong> {{ $user->dob }}
                @if (isset($requested['dob']) && $requested['dob'] !== $user->dob)
                    <br><span class="text-warning">Requested change: {{ $requested['dob'] }}</span>
                @endif
            </li>

            <li>
                <strong>Address:</strong> {{ $user->address }}
                @if (isset($requested['address']) && $requested['address'] !== $user->address)
                    <br><span class="text-warning">Requested change: {{ $requested['address'] }}</span>
                @endif
            </li>

            <li>
                <strong>Gender:</strong> {{ ucfirst($user->gender) }}
                @if (isset($requested['gender']) && $requested['gender'] !== $user->gender)
                    <br><span class="text-warning">Requested change: {{ ucfirst($requested['gender']) }}</span>
                @endif
            </li>

            <li>
                <strong>Status:</strong> {{ $user->status }}
                @if (isset($requested['status']) && $requested['status'] !== $user->status)
                    <br><span class="text-warning">Requested change: {{ $requested['status'] }}</span>
                @endif
            </li>

            <li>
                <strong>Phone:</strong> {{ $user->phone }}
                @if (isset($requested['phone']) && $requested['phone'] !== $user->phone)
                    <br><span class="text-warning">Requested change: {{ $requested['phone'] }}</span>
                @endif
            </li>

            @if ($user->role == 'student')
                <li>
                    <strong>Batch:</strong> {{ $user->batch }}
                    @if (isset($requested['batch']) && $requested['batch'] !== $user->batch)
                        <br><span class="text-warning">Requested change: {{ $requested['batch'] }}</span>
                    @endif
                </li>

                <li>
                    <strong>Semester:</strong> {{ $user->semester }}
                    @if (isset($requested['semester']) && $requested['semester'] !== $user->semester)
                        <br><span class="text-warning">Requested change: {{ $requested['semester'] }}</span>
                    @endif
                </li>
            @endif
        </ul>


        <form action="{{ route('admin.user.profileReview', $user->id) }}" method="POST" style="margin-top: 20px;">
            @csrf
            @method('PUT')

            <button type="submit" name="action" value="approve"
                style="background-color: #28a745; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; margin-right: 10px;">
                Approve
            </button>
            <br>
            <hr>
<br>
            <label for="reason"><strong>Decline Reason (if applicable):</strong></label><br>
            <textarea name="reason" id="reason" rows="4" style="width: 100%; margin-bottom: 15px;"></textarea>



            <button type="submit" name="action" value="decline"
                style="background-color: #dc3545; color: #fff; padding: 10px 15px; border: none; border-radius: 4px;">
                Decline
            </button>
        </form>

        <a href="{{ route('admin.user.review_index') }}" class="btn-back">Back</a>
    </div>
</div>

@include('backend.partials.footer')
