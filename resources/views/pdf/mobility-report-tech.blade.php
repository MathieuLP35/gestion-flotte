<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Rapport Analytique RSE</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000000;
            margin: 0;
            padding: 20px 30px;
            font-size: 11px;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 12px;
            margin: 0;
            color: #444444;
        }

        .logo {
            max-width: 140px;
            max-height: 60px;
            right: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f4f4f5;
            padding: 8px 12px;
            margin: 25px 0 15px 0;
            border-left: 4px solid #3f3f46;
            text-transform: uppercase;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #d4d4d8;
            padding: 10px;
            text-align: left;
        }

        table.data-table th {
            background-color: #f4f4f5;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            color: #3f3f46;
        }

        table.data-table td {
            font-size: 11px;
            color: #27272a;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .font-bold {
            font-weight: bold;
        }

        .kpi-summary {
            width: 100%;
            margin-bottom: 15px;
        }

        .kpi-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .kpi-box {
            border: 1px solid #d4d4d8;
            padding: 15px;
            width: 50%;
        }

        .kpi-box-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #71717a;
            margin: 0 0 5px 0;
        }

        .kpi-box-val {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #000000;
        }

        .kpi-delta {
            font-size: 11px;
            margin-top: 5px;
            font-weight: normal;
        }

        .pos {
            color: #16a34a;
        }

        .neg {
            color: #dc2626;
        }

        .note {
            border-top: 1px dashed #a1a1aa;
            padding-top: 10px;
            margin-top: 30px;
            font-size: 9px;
            color: #52525b;
            line-height: 1.4;
            text-align: justify;
        }

        .footer {
            text-align: right;
            font-size: 9px;
            color: #71717a;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @foreach($reports as $index => $report)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <div class="header">
            <table role="presentation">
                <tr>
                    <td style="width: 70%; vertical-align: top;">
                        <h1 class="title">Rapport de Conformité RSE</h1>
                        <p class="subtitle">Analyse d'impact environnemental de la flotte - Période: <strong>{{
                                $report['periodLabel'] }}</strong></p>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: top;">
                        @if($report['logoBase64'])
                        <img src="{{ $report['logoBase64'] }}" class="logo" alt="Logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">A. Synthèse des Indicateurs Globaux</div>
        <div class="kpi-summary">
            <table>
                <thead>
                    <tr>
                        <th scope="col" style="display:none;">Volume CO2 Évité</th>
                        <th scope="col" style="display:none;">Activité Covoiturage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="kpi-box" style="border-right: none;">
                            <p class="kpi-box-title">Volume d'Émissions CO2 Évitées (En KG)</p>
                            <p class="kpi-box-val">{{ $report['stats']['total_co2_saved'] }}</p>
                            @if($report['year'] !== 'all')
                            <p class="kpi-delta {{ $report['stats']['delta_co2'] >= 0 ? 'pos' : 'neg' }}">
                                {{ $report['stats']['delta_co2'] >= 0 ? '+' : '' }}{{ $report['stats']['delta_co2'] }}%
                                vs
                                période précédente
                            </p>
                            @endif
                        </td>
                        <td class="kpi-box">
                            <p class="kpi-box-title">Activité de Covoiturage (Trajets Validés)</p>
                            <p class="kpi-box-val">{{ $report['stats']['total_carpools'] }}</p>
                            @if($report['year'] !== 'all')
                            <p class="kpi-delta {{ $report['stats']['delta_carpools'] >= 0 ? 'pos' : 'neg' }}">
                                {{ $report['stats']['delta_carpools'] >= 0 ? '+' : '' }}{{
                                $report['stats']['delta_carpools'] }}% vs période précédente
                            </p>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section-title">B. Performance Carbone par Centre (Agences)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Nom du Centre / Agence</th>
                    <th style="width: 25%;" class="text-center">Trajets Mutualisés</th>
                    <th style="width: 25%;" class="text-right">CO2 Évité (kg)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['agencesStats'] as $agence)
                <tr>
                    <td class="font-bold">{{ $agence['name'] }}</td>
                    <td class="text-center">{{ $agence['carpools_count'] }}</td>
                    <td class="text-right font-bold">{{ $agence['co2_saved'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #71717a;">Aucune donnée agence disponible.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">C. Structure Énergétique du Parc Automobile</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 75%;">Catégorie de Motorisation</th>
                    <th style="width: 25%;" class="text-right">Volume d'Actifs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['vehicleEnergyStats'] as $energy)
                <tr>
                    <td style="text-transform: capitalize;">{{ $energy['energie'] }}</td>
                    <td class="text-right font-bold">{{ $energy['total'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center" style="color: #71717a;">Aucun véhicule dans la flotte.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            <strong>NOTE MÉTHODOLOGIQUE DE CALCUL :</strong> Les estimations d'émissions de CO2 évitées sont basées sur
            le benchmark d'émissions moyennes réglementaires appliquées aux distances parcourues enregistrées. (Base de
            calcul : 130g CO2/km pour Moteur Thermique ; 70g CO2/km pour Véhicules Hybrides Rechargeables ; 0g CO2/km
            pour Motorisation 100% Électrique en utilisation d'entreprise contrôlée). L'amortissement CO2 lié à
            l'utilisation mutualisée (covoiturage inter-collaborateurs certifié via l'outil) s'applique mathématiquement
            au pro-rata des passagers uniques enregistrés et validés formellement à l'issue de chaque trajet planifié
            dans le système.
        </div>

        <div class="footer">
            Document généré par le système d'information de gestion de flotte (Outil RH) - Édité le {{
            now()->format('d/m/Y H:i') }} - Page {{ $index + 1 }}
        </div>
    </div>
    @endforeach
</body>

</html>