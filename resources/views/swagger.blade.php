<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'FleetBe') }} - Swagger API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5/favicon-32x32.png" sizes="32x32" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .swagger-custom-header {
            background: #1e293b;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #334155;
        }
        .swagger-custom-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            text-decoration: none;
        }
        .swagger-custom-header .brand span {
            color: #38bdf8;
        }
        .swagger-custom-header .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .swagger-custom-header .nav-links a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .swagger-custom-header .nav-links a:hover,
        .swagger-custom-header .nav-links a.active {
            color: #ffffff;
            background: #334155;
        }
        .swagger-ui .topbar {
            display: none !important;
        }
        .swagger-ui .info {
            margin: 24px 0;
        }
        .swagger-ui .btn.authorize {
            border-color: #10b981;
            color: #10b981;
        }
        .swagger-ui .btn.authorize svg {
            fill: #10b981;
        }
    </style>
</head>
<body>
    <header class="swagger-custom-header">
        <a href="{{ url('/') }}" class="brand">
            🚗 {{ config('app.name', 'FleetBe') }} <span>Swagger UI</span>
        </a>
        <nav class="nav-links">
            <a href="{{ url('/swagger') }}" class="active">Swagger UI</a>
            <a href="{{ url('/scalar') }}">Scalar Docs</a>
            <a href="{{ url('/docs') }}">Scribe Docs</a>
            <a href="{{ url('/docs.openapi') }}" target="_blank">OpenAPI Spec</a>
        </nav>
    </header>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "{{ url('/docs.openapi') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                persistAuthorization: true,
                displayRequestDuration: true,
                docExpansion: "list",
                filter: true,
                showExtensions: true,
                showCommonExtensions: true,
                tryItOutEnabled: true,
            });
        };
    </script>
</body>
</html>
