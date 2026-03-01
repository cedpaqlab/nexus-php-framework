@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
<section class="admin-section">
    <div class="section-header">
        <h1>Manage Users</h1>
        <a href="/admin/users/create" class="btn btn-primary">Create User</a>
    </div>

    <div class="filters">
        <form method="GET" action="/admin/users">
            <select name="role">
                <option value="">All Roles</option>
                <option value="user" {{ ($filters['role'] ?? '') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ ($filters['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users ?? [] as $user)
            <tr>
                <td>{{ $user['id'] ?? '' }}</td>
                <td>{{ $user['name'] ?? '' }}</td>
                <td>{{ $user['email'] ?? '' }}</td>
                <td>
                    <span class="role-badge role-{{ $user['role'] ?? 'user' }}">{{ $user['role'] ?? 'user' }}</span>
                </td>
                <td>{{ $user['created_at'] ?? '' }}</td>
                <td class="actions">
                    <a href="/admin/users/{{ $user['id'] ?? '' }}/edit" class="btn-small">Edit</a>
                    <button type="button" onclick="deleteUser({{ $user['id'] ?? 0 }})" class="btn-small btn-danger">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

<script>
function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    $.ajax({
        url: '/admin/users/' + id,
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        success: function() {
            location.reload();
        },
        error: function() {
            alert('Error deleting user');
        }
    });
}
</script>
@endsection
