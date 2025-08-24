<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-leaf me-2"></i><?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    
                    <!-- People Dropdown -->
                    <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_PASTOR) || hasPermission(ROLE_COACH) || hasPermission(ROLE_MENTOR)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="peopleDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users me-1"></i>People
                        </a>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="peopleDropdown">
                            <?php if (hasPermission(ROLE_SUPER_ADMIN)): ?>
                            <li>
                                <a class="dropdown-item" href="/church">
                                    <i class="fas fa-church me-2"></i>Churches
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/pastor">
                                    <i class="fas fa-user-tie me-2"></i>Pastors
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_PASTOR)): ?>
                            <li>
                                <a class="dropdown-item" href="/coach">
                                    <i class="fas fa-users me-2"></i>Coaches
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_COACH)): ?>
                            <li>
                                <a class="dropdown-item" href="/mentor">
                                    <i class="fas fa-user-graduate me-2"></i>Mentors
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_MENTOR)): ?>
                            <li>
                                <a class="dropdown-item" href="/lifegroup">
                                    <i class="fas fa-users me-2"></i>Lifegroups
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_MENTOR)): ?>
                            <li>
                                <a class="dropdown-item" href="/member">
                                    <i class="fas fa-user me-2"></i>Members
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN)): ?>
                            <li>
                                <a class="dropdown-item" href="/friend">
                                    <i class="fas fa-user-plus me-2"></i>New Friend
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Events Dropdown -->
                    <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_PASTOR) || hasPermission(ROLE_COACH) || hasPermission(ROLE_MENTOR)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="eventsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-calendar-alt me-1"></i>Events
                        </a>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="eventsDropdown">
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_PASTOR)): ?>
                            <li>
                                <a class="dropdown-item" href="/events/church">
                                    <i class="fas fa-church me-2"></i>Church Events
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_COACH)): ?>
                            <li>
                                <a class="dropdown-item" href="/events/satelife">
                                    <i class="fas fa-satellite me-2"></i>Satelife Events
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (hasPermission(ROLE_SUPER_ADMIN) || hasPermission(ROLE_MENTOR)): ?>
                            <li>
                                <a class="dropdown-item" href="/events/lifegroup">
                                    <i class="fas fa-users me-2"></i>Lifegroup Events
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                <?php if (hasPermission(ROLE_SUPER_ADMIN)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="systemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-gear me-1"></i>System Settings
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="systemDropdown">
                            <li>
                                <a class="dropdown-item" href="/settings">
                                    <i class="fas fa-sliders-h me-2"></i>Settings Home
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/settings#member-statuses">
                                    <i class="fas fa-user-check me-2"></i>Member Statuses
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= $_SESSION['user_name'] ?>
                        </a>
                        <ul class="dropdown-menu shadow-sm">
                            <li>
                                <a class="dropdown-item" href="/auth/profile">
                                    <i class="fas fa-user-edit me-2"></i>Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/auth/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= $flash['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container my-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
    <!-- Pagination JS -->
    <script src="/assets/js/pagination.js"></script>
</body>
</html> 