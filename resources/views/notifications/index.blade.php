@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell me-2"></i>Notifications
        </h1>
        <div>
            <a href="{{ route('notifications.settings') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Paramètres
            </a>
            <button id="markAllAsRead" class="btn btn-primary">
                <i class="fas fa-check-double"></i> Tout marquer comme lu
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune notification</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-start flex-grow-1">
                                    <div class="notification-icon me-3">
                                        <i class="{{ $notification->data['icon'] ?? 'fas fa-bell' }} fa-2x text-{{ $notification->data['color'] ?? 'info' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-1">
                                                {{ $notification->data['title'] ?? 'Notification' }}
                                                @if(!$notification->read_at)
                                                    <span class="badge bg-primary ms-2">Nouveau</span>
                                                @endif
                                            </h5>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>

                                        @if(isset($notification->data['url']) && $notification->data['url'] !== '#')
                                            <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="fas fa-eye"></i> Voir le détail
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if(!$notification->read_at)
                                            <li>
                                                <a class="dropdown-item mark-as-read" href="#" data-id="{{ $notification->id }}">
                                                    <i class="fas fa-check"></i> Marquer comme lu
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marquer toutes les notifications comme lues
    document.getElementById('markAllAsRead')?.addEventListener('click', function() {
        fetch('{{ route("notifications.markAllAsRead") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });

    // Marquer une notification comme lue
    document.querySelectorAll('.mark-as-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;

            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        });
    });
});
</script>
@endpush
@endsection
