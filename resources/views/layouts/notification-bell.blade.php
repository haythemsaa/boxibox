<!-- Cloche de notifications -->
<div class="dropdown" id="notificationDropdown">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <span class="badge bg-danger badge-counter position-absolute top-0 start-100 translate-middle"
              id="notificationCount" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" style="min-width: 350px;">
        <h6 class="dropdown-header bg-primary text-white">
            <i class="fas fa-bell me-2"></i>Notifications
        </h6>

        <div id="notificationList" class="overflow-auto" style="max-height: 400px;">
            <!-- Les notifications seront chargées ici via AJAX -->
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        </div>

        <div class="dropdown-divider"></div>

        <div class="d-flex justify-content-between px-3 py-2">
            <a href="#" id="markAllRead" class="small text-decoration-none">
                <i class="fas fa-check-double"></i> Tout marquer comme lu
            </a>
            <a href="{{ route('notifications.index') }}" class="small text-decoration-none">
                <i class="fas fa-list"></i> Voir tout
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Fonction pour charger les notifications
function loadNotifications() {
    fetch('{{ route("notifications.getUnread") }}')
        .then(response => response.json())
        .then(data => {
            const notificationCount = document.getElementById('notificationCount');
            const notificationList = document.getElementById('notificationList');

            // Mettre à jour le compteur
            if (data.count > 0) {
                notificationCount.textContent = data.count;
                notificationCount.style.display = 'inline-block';
            } else {
                notificationCount.style.display = 'none';
            }

            // Mettre à jour la liste
            if (data.notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <p class="mb-0 small">Aucune notification</p>
                    </div>
                `;
            } else {
                notificationList.innerHTML = data.notifications.map(notification => `
                    <a class="dropdown-item d-flex align-items-start notification-item"
                       href="${notification.url}"
                       data-id="${notification.id}">
                        <div class="me-3">
                            <i class="${notification.icon} text-${notification.color}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-gray-500">${notification.created_at}</div>
                            <span class="font-weight-bold">${notification.title}</span>
                            <div class="small">${notification.message}</div>
                        </div>
                    </a>
                `).join('');

                // Ajouter l'événement pour marquer comme lu au clic
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        const notificationId = this.dataset.id;
                        markAsRead(notificationId);
                    });
                });
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des notifications:', error);
        });
}

// Fonction pour marquer une notification comme lue
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });
}

// Fonction pour marquer toutes les notifications comme lues
document.addEventListener('DOMContentLoaded', function() {
    // Charger les notifications au chargement de la page
    loadNotifications();

    // Recharger les notifications toutes les 30 secondes
    setInterval(loadNotifications, 30000);

    // Marquer toutes comme lues
    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.preventDefault();
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
                loadNotifications();
            }
        });
    });
});
</script>
@endpush
