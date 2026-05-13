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

    /* Tableaux */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    .table th,
    .table td {
      padding: 6px 8px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
      vertical-align: top;
    }

    .table th {
      background-color: #f9fafb;
      font-weight: bold;
      font-size: 9pt;
      color: #4b5563;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .table td {
      font-size: 10pt;
    }

    .table.compact th,
    .table.compact td {
      padding: 4px 6px;
      font-size: 9pt;
    }

    .table .num {
      font-family: 'Courier New', monospace;
      font-size: 9pt;
      color: #6b7280;
    }

    .table .muted {
      color: #6b7280;
      font-size: 9pt;
    }

    /* Sous-titre de section pour grouper des tableaux */
    .group-title {
      font-size: 10pt;
      font-weight: bold;
      color: #4b5563;
      margin-top: 12px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* État vide pour une section liste */
    .empty-state {
      padding: 10px;
      text-align: center;
      font-size: 10pt;
      color: #9ca3af;
      font-style: italic;
      background-color: #f9fafb;
      border-radius: 4px;
    }

    /* Pastilles de statut */
    .pill {
      display: inline-block;
      padding: 2px 7px;
      border-radius: 999px;
      font-size: 8.5pt;
      font-weight: 600;
      line-height: 1.2;
      white-space: nowrap;
    }

    .pill-default { background: #f1f5f9; color: #475569; }
    .pill-approved { background: #d1fae5; color: #065f46; }
    .pill-pending { background: #fef3c7; color: #92400e; }
    .pill-rejected { background: #fee2e2; color: #991b1b; }
    .pill-info { background: #dbeafe; color: #1e40af; }
    .pill-violet { background: #ede9fe; color: #5b21b6; }

    /* Indicateur de compteur dans le titre de section */
    .section-count {
      font-size: 10pt;
      font-weight: normal;
      color: #6b7280;
      margin-left: 6px;
    }

    /* Forcer un saut de page avant une section */
    .section.page-break {
      page-break-before: always;
    }

    /* Permettre de couper une section longue */
    .section.allow-break {
      page-break-inside: auto;
    }

    /* Éviter une rupture au milieu d'une ligne de tableau */
    .table tr {
      page-break-inside: avoid;
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
