@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1><i class="bi bi-person-plus me-2"></i>{{ isset($user) ? 'Edit User' : 'Create User' }}</h1>
    </div>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
                @csrf
                @isset($user) @method('PUT') @endisset

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="user" {{ old('role', $user->role ?? '') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="viewer" {{ old('role', $user->role ?? '') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password {{ isset($user) ? '(leave blank to keep)' : '' }}</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" {{ isset($user) ? '' : 'required' }}>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ isset($user) ? 'Update' : 'Create' }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
