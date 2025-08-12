<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <!-- Bootstrap (via Vite or CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Wirechat styles (must be included) -->
  @wirechatStyles
  <!-- Optional: set Wirechat theme vars after @wirechatStyles -->
  <style>
    :root{
      --wc-brand-primary: #0d6efd; /* match Bootstrap primary */
      /* …other --wc-... variables as needed… */
    }
  </style>
</head>
<body>
  <div class="container py-4" style="height: calc(100vh - 4rem);">
    {{ $slot }}
  </div>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Wirechat JS + Livewire hooks -->
  @wirechatAssets
</body>
</html>