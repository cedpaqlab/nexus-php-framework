@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<section class="admin-section">
    <h1>Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🛡️</div>
            <div class="stat-content">
                <h3>{{ $stats['admin_count'] ?? 0 }}</h3>
                <p>Admins</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👤</div>
            <div class="stat-content">
                <h3>{{ $stats['user_count'] ?? 0 }}</h3>
                <p>Regular Users</p>
            </div>
        </div>
    </div>

    <div class="admin-actions">
        <a href="/admin/users" class="btn btn-primary">Manage Users</a>
        <a href="/admin/users/create" class="btn btn-secondary">Create User</a>
    </div>

    <div class="recent-users">
        <h2>Recent Users</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($users ?? [], 0, 10) as $u)
                <tr>
                    <td>{{ $u['id'] ?? '' }}</td>
                    <td>{{ $u['name'] ?? '' }}</td>
                    <td>{{ $u['email'] ?? '' }}</td>
                    <td><span class="role-badge role-{{ $u['role'] ?? 'user' }}">{{ $u['role'] ?? 'user' }}</span></td>
                    <td>
                        <a href="/admin/users/{{ $u['id'] ?? '' }}/edit" class="btn-small">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
