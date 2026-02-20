<!-- Header -->
<header class="header d-flex justify-content-between align-items-center p-3 bg-light border-bottom position-sticky" style="top: 0; z-index: 99; transition: margin-left 0.3s;">
    <div class="header-left d-flex align-items-center gap-3">
        <span id="sidebarHamburgerHeader" class="hamburger-btn" role="button" tabindex="0" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Toggle navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
            </svg>
            <span class="visually-hidden">Menu</span>
        </span>
        <div>
            <h1 class="page-title h4 mb-1"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
            <p class="page-subtitle text-muted small mb-0"><?php echo isset($pageSubtitle) ? $pageSubtitle : 'Overall Overview of Operational Status'; ?></p>
        </div>
    </div>

    <div class="header-right d-flex align-items-center gap-3">
        <div class="notification-icon position-relative">
            <span role="button" tabindex="0" class="icon-trigger" data-bs-toggle="modal" data-bs-target="#messagesModal" aria-label="Open messages">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chat-dots-fill" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg>
            </span>
            <span class="notification-badge badge bg-danger position-absolute top-0 start-100 translate-middle">1</span>
        </div>
        <div class="notification-icon position-relative">
            <span role="button" tabindex="0" class="icon-trigger" data-bs-toggle="modal" data-bs-target="#notificationsModal" aria-label="Open notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/>
                </svg>
            </span>
            <span class="notification-badge badge bg-danger position-absolute top-0 start-100 translate-middle">2</span>
        </div>
        <div class="user-profile d-flex align-items-center gap-2">
            <span>Staff Name</span>
            <div class="profile-pic bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <span class="text-white">S</span>
            </div>
        </div>
    </div>
</header>

<!-- Messages Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messagesModalLabel">Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You have 1 new message.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationsModalLabel">Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                You have 2 new notifications.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
