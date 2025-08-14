<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Bilan Client')</title>
  <style>
    /* Reset et base */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Arial', sans-serif;
      font-size: 11pt;
      line-height: 1.4;
      color: #374151;
      background: white;
    }

    /* Configuration des pages */
    @page {
      size: A4;
      margin: 20mm 20mm 30mm 20mm;
      /* top right bottom left */
    }

    /* Header avec logo */
    .header {
      text-align: center;
      margin-bottom: 50px;
      border-bottom: 2px solid #1f2937;
      padding-bottom: 20px;
    }

    .logo {
      width: 160px;
      height: auto;
      margin: 0 auto 25px;
      display: block;
    }

    .document-title {
      font-size: 24pt;
      font-weight: bold;
      color: #1f2937;
      margin-bottom: 5px;
    }

    .company-name {
      font-size: 16pt;
      color: #6b7280;
      margin-bottom: 10px;
    }

    .generation-date {
      font-size: 10pt;
      color: #9ca3af;
    }

    /* Sections */
    .section {
      margin-bottom: 45px;
      page-break-inside: avoid;
    }

    .section-title {
      font-size: 14pt;
      font-weight: bold;
      color: #1f2937;
      margin-bottom: 12px;
      padding-bottom: 5px;
      border-bottom: 1px solid #d1d5db;
    }

    .subsection-title {
      font-size: 12pt;
      font-weight: bold;
      color: #374151;
      margin-bottom: 8px;
      margin-top: 15px;
    }

    /* Grilles d'informations */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }

    .info-grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 12px;
      margin-bottom: 15px;
    }

    .info-item {
      margin-bottom: 8px;
    }

    .info-label {
      font-weight: bold;
      color: #6b7280;
      font-size: 10pt;
      margin-bottom: 3px;
    }

    .info-value {
      color: #374151;
      font-size: 11pt;
      word-wrap: break-word;
    }

    .info-value.empty {
      color: #9ca3af;
      font-style: italic;
    }

    /* Saut de page simple */
    .page-break {
      page-break-before: always;
    }

    /* Footer */
    @page {
      @bottom-center {
        content: "@yield('footer', 'Document confidentiel - Ne peut être diffusé à des tiers sans accord préalable')";
        font-size: 9pt;
        color: #6b7280;
        border-top: 1px solid #d1d5db;
        padding-top: 5px;
      }
    }

    /* Tableaux si nécessaire */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .table th,
    .table td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #d1d5db;
    }

    .table th {
      background-color: #f9fafb;
      font-weight: bold;
      font-size: 10pt;
    }

    .table td {
      font-size: 10pt;
    }
  </style>
</head>

<body>
  <!-- Header sur première page seulement -->
  <div class="header">
    <img src="{{ public_path('images/aeropaperasse-logo.png') }}" alt="Logo Aéropaperasse" class="logo">
    <h1 class="document-title">@yield('document-title', 'BILAN DE SOCIÉTÉ')</h1>
    <div class="company-name">@yield('company-name')</div>
    <div class="generation-date">Document généré le {{ \Carbon\Carbon::now('Europe/Paris')->format('d/m/Y à H:i') }}
    </div>
  </div>

  <!-- Contenu principal -->
  @yield('content')
</body>

</html>
