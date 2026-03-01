@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard-section">
    <h1>Welcome to Your Dashboard</h1>

    <div class="user-info-card">
        <h2>Your Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID:</span>
                <span class="info-value">{{ $user['id'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user['email'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Role:</span>
                <span class="info-value">
                    <span class="role-badge role-{{ $user['role'] ?? 'user' }}">{{ $user['role'] ?? 'user' }}</span>
                </span>
            </div>
        </div>
    </div>
</section>
@endsection
