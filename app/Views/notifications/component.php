<!-- Notification Badge & Dropdown Component -->
<!-- Include this in your main layout/header file -->

<!-- Notification Bell Icon with Badge -->
<li class="nav-item dropdown notification-dropdown">
    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <span class="badge bg-danger notification-badge" id="notificationBadge" style="display: none;">0</span>
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifikasi</span>
            <a href="#" class="btn btn-sm btn-link text-decoration-none" id="markAllReadBtn" style="font-size: 12px;">Tandai semua dibaca</a>
        </li>
        <li><hr class="dropdown-divider"></li>
        
        <!-- Loading State -->
        <li id="notificationLoading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </li>
        
        <!-- Empty State -->
        <li id="notificationEmpty" class="text-center py-4" style="display: none;">
            <i class="bi bi-bell-slash" style="font-size: 2rem; color: #ccc;"></i>
            <p class="text-muted mt-2 mb-0">Tidak ada notifikasi</p>
        </li>
        
        <!-- Notifications List -->
        <div id="notificationList"></div>
        
        <li><hr class="dropdown-divider"></li>
        <li class="text-center p-2">
            <a href="<?= site_url('notifications') ?>" class="btn btn-sm btn-outline-primary w-100">Lihat Semua</a>
        </li>
    </ul>
</li>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const notificationLoading = document.getElementById('notificationLoading');
    const notificationEmpty = document.getElementById('notificationEmpty');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Load notifications on page load
    loadNotifications();
    
    // Auto-refresh every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Load notifications function
    function loadNotifications() {
        fetch('<?= site_url('notifications/get-list') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error loading notifications:', data.error);
                    return;
                }
                
                updateBadge(data.unread_count);
                renderNotifications(data.notifications);
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Update badge count
    function updateBadge(count) {
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.style.display = 'inline-block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }
    
    // Render notifications list
    function renderNotifications(notifications) {
        notificationLoading.style.display = 'none';
        
        if (!notifications || notifications.length === 0) {
            notificationEmpty.style.display = 'block';
            notificationList.innerHTML = '';
            return;
        }
        
        notificationEmpty.style.display = 'none';
        
        let html = '';
        notifications.forEach(notif => {
            const typeClass = getTypeClass(notif.type);
            const icon = getTypeIcon(notif.type);
            const timeAgo = getTimeAgo(notif.created_at);
            
            html += `
                <li class="notification-item ${!notif.is_read ? 'unread' : ''}" data-id="${notif.id}">
                    <a href="${notif.url}" class="dropdown-item d-flex align-items-start p-3 ${!notif.is_read ? 'bg-light' : ''}" style="text-decoration: none; color: inherit;" onclick="markAsRead(${notif.id}, event)">
                        <div class="flex-shrink-0">
                            <i class="bi ${icon} fs-5 ${typeClass}"></i>
                        </div>
                        <div class="ms-2 flex-grow-1">
                            <h6 class="mb-1 fw-bold ${!notif.is_read ? 'text-dark' : 'text-muted'}">${escapeHtml(notif.title)}</h6>
                            <p class="mb-1 small text-muted">${escapeHtml(notif.message).substring(0, 80)}${notif.message.length > 80 ? '...' : ''}</p>
                            <span class="badge ${!notif.is_read ? 'bg-primary' : 'bg-secondary'} bg-opacity-10 text-${!notif.is_read ? 'primary' : 'secondary'}" style="font-size: 10px;">${timeAgo}</span>
                        </div>
                        ${!notif.is_read ? '<span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px; min-width: 8px;"></span>' : ''}
                    </a>
                </li>
            `;
        });
        
        notificationList.innerHTML = html;
    }
    
    // Mark as read function
    window.markAsRead = function(id, event) {
        event.preventDefault();
        
        fetch('<?= site_url('notifications/mark-as-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove unread indicator
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.querySelector('.dropdown-item').classList.remove('bg-light');
                    item.querySelector('.badge.rounded-circle')?.remove();
                    
                    // Update text colors
                    const title = item.querySelector('h6');
                    if (title) {
                        title.classList.remove('text-dark');
                        title.classList.add('text-muted');
                    }
                }
                
                // Reload to update badge
                loadNotifications();
            }
        });
    };
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        fetch('<?= site_url('notifications/mark-all-as-read') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        });
    });
    
    // Helper functions
    function getTypeClass(type) {
        const classes = {
            'info': 'text-info',
            'warning': 'text-warning',
            'danger': 'text-danger',
            'success': 'text-success'
        };
        return classes[type] || 'text-info';
    }
    
    function getTypeIcon(type) {
        const icons = {
            'info': 'bi-info-circle',
            'warning': 'bi-exclamation-triangle',
            'danger': 'bi-exclamation-octagon',
            'success': 'bi-check-circle'
        };
        return icons[type] || 'bi-bell';
    }
    
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Baru saja';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' menit yang lalu';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' jam yang lalu';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' hari yang lalu';
        
        return date.toLocaleDateString('id-ID');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<style>
.notification-dropdown {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 10px;
    padding: 2px 5px;
    border-radius: 10px;
}

.notification-menu {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
}

.notification-item:hover {
    background-color: #f8f9fa !important;
}

.notification-item.unread {
    border-left: 3px solid #3498db;
}
</style>
