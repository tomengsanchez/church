<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        <h1 class="display-1 text-muted mt-3">404</h1>
                        <h2 class="h4 text-muted mb-4">Page Not Found</h2>
                        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
                        <div class="d-grid gap-2 d-md-block">
                            <a href="/" class="btn btn-primary me-md-2">
                                <i class="fas fa-home me-2"></i>Go Home
                            </a>
                            <a href="/auth/login" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 