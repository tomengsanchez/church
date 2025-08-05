<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a148c;
            --secondary-color: #00838f;
            --accent-color: #00c853;
            --light-color: #ffffff;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: var(--light-color);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .error-code {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #3a0d6b;
            border-color: #3a0d6b;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #006064;
            border-color: #006064;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <div class="error-code">500</div>
        <div class="error-title">Server Error</div>
        
        <div class="error-message">
            <p>We're experiencing some technical difficulties. Our team has been notified and is working to resolve the issue.</p>
            <p>Please try again in a few moments. If the problem persists, please contact support.</p>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="/" class="btn btn-primary me-md-2">
                <i class="fas fa-home me-2"></i>Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                Error ID: <?= uniqid() ?> | 
                Time: <?= date('Y-m-d H:i:s') ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 