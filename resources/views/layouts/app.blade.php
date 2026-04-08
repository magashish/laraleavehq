<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LeaveHQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .mobile-header {
            display: none;
            align-items: center;
            gap: 12px;
            background: #83acdb;
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .mobile-brand {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hamburger {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 2px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            cursor: pointer;
            padding: 2px;
            flex-shrink: 0;
            align-items: center;
        }
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 98;
        }
        @media (max-width: 768px) {
            .mobile-header  { display: flex; }
            .sidebar-close  { display: flex; }
            .app  { display: block; height: auto; overflow: visible; }
            .main { min-height: 100vh; overflow-y: auto; }
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100vh;
                width: 240px;
                z-index: 99;
                transition: left 0.25s ease;
                overflow-y: auto;
            }
            .sidebar.sidebar-open { left: 0 !important; }
        }
    </style>
</head>
<body>
<div class="app" x-data="{ sidebarOpen: false }">

    {{-- Mobile top bar --}}
    <div class="mobile-header">
        <button class="hamburger" @click="sidebarOpen = true" aria-label="Open menu">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <span class="mobile-brand">Pinnacle Internet Marketing</span>
    </div>

    {{-- Sidebar backdrop --}}
    <div class="sidebar-backdrop" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity></div>

    {{-- Sidebar --}}
    <aside class="sidebar" :class="sidebarOpen ? 'sidebar-open' : ''">
        <div class="sidebar-logo">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <h1>Pinnacle Internet Marketing</h1>
                <button class="sidebar-close" @click="sidebarOpen = false" aria-label="Close menu">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <p>Leave management</p>
        </div>

        <div class="sidebar-section">Menu</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" @click="sidebarOpen = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="{{ route('leave.index') }}" class="nav-item {{ request()->routeIs('leave.*') ? 'active' : '' }}" @click="sidebarOpen = false">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ Auth::user()->isManager() ? 'Manage Leave' : 'My Leave' }}
        </a>
        @if(Auth::user()->isManager())
            <a href="{{ route('team.index') }}" class="nav-item {{ request()->routeIs('team.*') ? 'active' : '' }}" @click="sidebarOpen = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Team
            </a>
            <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" @click="sidebarOpen = false">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
            </a>
        @endif

        <div class="user-info">
            @php $u = Auth::user(); @endphp
            @if($u->profile_photo)
                <img src="{{ $u->photoUrl() }}" alt="{{ $u->name }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            @else
                <div class="avatar" style="width:34px;height:34px;font-size:12px;background:{{ $u->color }}33;color:{{ $u->color }}">
                    {{ $u->initials() }}
                </div>
            @endif
            <div style="flex:1;min-width:0;">
                <a href="{{ route('profile.edit') }}" style="text-decoration:none;">
                    <div class="name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->name }}</div>
                    <div class="role">{{ $u->roleBadgeLabel() }} &bull; {{ $u->role }}</div>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-logout">Sign out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="main">
        @if(session('success'))
            <div style="padding:16px 32px 0;">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div style="padding:16px 32px 0;">
                <div class="alert alert-error">{{ session('error') }}</div>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
</body>
</html>

