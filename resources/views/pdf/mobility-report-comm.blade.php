<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Impact RSE - Mobilité Durable</title>
    <style>
        @page {
            margin: 0px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            background-color: #f8fafc;
        }

        .page-break {
            page-break-after: always;
        }

        .hero {
            background-color: #047857;
            color: #ffffff;
            padding: 40px 50px 80px 50px;
            text-align: center;
        }

        .hero table {
            width: 100%;
        }

        .hero .logo-col {
            text-align: left;
            width: 30%;
        }

        .hero .date-col {
            text-align: right;
            width: 30%;
            font-size: 12px;
            color: #a7f3d0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .logo {
            max-width: 130px;
            max-height: 60px;
            filter: brightness(0) invert(1);
        }

        .hero-title {
            font-size: 38px;
            font-weight: 800;
            margin: 30px 0 10px 0;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #d1fae5;
            font-weight: 300;
            margin: 0;
        }

        .content {
            padding: 0 50px;
            margin-top: -40px;
        }

        .kpi-container {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
            margin-left: -20px;
            width: calc(100% + 40px);
        }

        .kpi-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            width: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 15px;
            border-radius: 50%;
            display: inline-block;
            line-height: 40px;
            font-size: 20px;
        }

        .kpi-icon.co2 {
            background-color: #d1fae5;
            color: #059669;
        }

        .kpi-icon.carpool {
            background-color: #e0e7ff;
            color: #4f46e5;
        }

        .kpi-value {
            font-size: 56px;
            font-weight: 900;
            margin: 0;
            line-height: 1;
            letter-spacing: -2px;
        }

        .kpi-value.co2 {
            color: #059669;
        }

        .kpi-value.carpool {
            color: #4f46e5;
        }

        .kpi-label {
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        .details-box {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 35px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .details-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-text {
            font-size: 15px;
            color: #475569;
            text-align: center;
            line-height: 1.7;
            margin: 0;
        }

        .highlight {
            color: #047857;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            padding: 20px 0;
            background-color: #f1f5f9;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>
    @foreach($reports as $index => $report)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">

        <!-- HEADER / HERO -->
        <div class="hero">
            <table role="presentation">
                <tr>
                    <td class="logo-col">
                        @if($report['logoBase64'])
                        <img src="{{ $report['logoBase64'] }}" class="logo" alt="Logo">
                        @else
                        <span style="font-weight: bold; font-size: 18px;">RSE Flotte</span>
                        @endif
                    </td>
                    <td style="width: 40%;"></td>
                    <td class="date-col">
                        Période : {{ $report['periodLabel'] }}
                    </td>
                </tr>
            </table>

            <h1 class="hero-title">Notre Impact Positif</h1>
            <p class="hero-subtitle">Ensemble, nous faisons la différence pour l'environnement.</p>
        </div>

        <!-- MAIN CARDS -->
        <div class="content">
            <table class="kpi-container">
                <thead>
                    <tr>
                        <th scope="col" style="display:none;">Indicateur CO2</th>
                        <th scope="col" style="display:none;">Indicateur Covoiturage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="kpi-card" style="border-bottom: 4px solid #10b981;">
                            <p class="kpi-value co2">{{ $report['stats']['total_co2_saved'] }}</p>
                            <p class="kpi-label">KG de CO2 Évités</p>
                        </td>
                        <td class="kpi-card" style="border-bottom: 4px solid #6366f1;">
                            <p class="kpi-value carpool">{{ $report['stats']['total_carpools'] }}</p>
                            <p class="kpi-label">Trajets Partagés</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- MESSAGE BOX -->
            <div class="details-box">
                <h3 class="details-title">Une démarche d'équipe et d'engagement</h3>
                <p class="details-text">
                    La réduction de notre empreinte carbone n'est pas qu'un simple objectif, c'est une
                    <span class="highlight">réussite collective</span>. Grâce à votre adoption du covoiturage
                    d'entreprise et
                    à l'optimisation continue de nos véhicules, nous préservons activement la qualité de notre
                    environnement !
                </p>
                <div style="text-align: center; margin-top: 25px;">
                    <span
                        style="display: inline-block; padding: 8px 16px; background-color: #f0fdf4; color: #166534; font-weight: bold; border-radius: 20px; font-size: 13px;">
                        Merci à tous pour votre implication !
                    </span>
                </div>
            </div>
        </div>

    </div>
    @endforeach

    <div class="footer">
        Bilan Interne RSE - Empreinte Carbone de la Mobilité - Édité le {{ now()->format('d/m/Y') }}
    </div>
</body>

</html>