<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Bitácora Diaria - Shalom</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            padding: 20px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            background-color: white;
        }

        /* Header Section */
        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .logo-cell {
            width: 180px;
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .info-cell {
            padding-left: 20px;
            vertical-align: top;
        }

        .company-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .info-row {
            margin-bottom: 12px;
            font-size: 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0 10px;
            border: none;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }

        .info-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 150px;
            text-align: center;
        }

        /* Table Section */
        .table-wrapper {
            margin: 20px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #333;
            font-size: 10px;
        }

        .data-table thead {
            background-color: #f0f0f0;
        }

        .data-table th {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            height: 30px;
            font-size: 9px;
        }

        .data-table td {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
            height: 25px;
        }

        .row-number {
            background-color: #f9f9f9;
            font-weight: bold;
            width: 25px;
        }

        /* Footer Section */
        .footer-section {
            margin-top: 20px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .footer-text {
            font-size: 10px;
            font-weight: bold;
        }

        .total-box {
            border: 2px solid #333;
            padding: 5px 10px;
            text-align: center;
            width: 250px;
            font-weight: bold;
            float: right;
        }

        .total-label {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .total-value {
            border-bottom: 1px solid #333;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <div style="font-size: 24px; font-weight: bold;">SHALOM</div>
                    </td>
                    <td class="info-cell">
                        <div class="company-title">TRANSPORTES EJECUTIVOS SHALOM S.A. DE C.V.</div>
                        <div class="report-title">REPORTE DE BITACORA DIARIA HOTELES MARRIOTT</div>
                        
                        <table class="info-table">
                            <tr>
                                <td>
                                    <span class="info-label">TURNO:</span>
                                    <span class="info-line">&nbsp;</span>
                                </td>
                                <td>
                                    <span class="info-label">COORDINADOR:</span>
                                    <span class="info-line">{{ $trips->first()->creator->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="info-label">FECHA:</span>
                                    <span class="info-line">{{ $date }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Table -->
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">ITEM</th>
                        <th style="width: 50px;">HORA</th>
                        <th style="width: 40px;">NO. PAS</th>
                        <th>SALIDA</th>
                        <th>DISTRITO</th>
                        <th>LLEGADA</th>
                        <th style="width: 30px;">E</th>
                        <th style="width: 30px;">C.H</th>
                        <th style="width: 60px;">MONTO</th>
                        <th>CONDUCTOR</th>
                        <th style="width: 30px;">TJ</th>
                        <th style="width: 30px;">IDA</th>
                        <th style="width: 30px;">VTA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trips as $index => $trip)
                    <tr>
                        <td class="row-number">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($trip->trip_time)->format('H:i') }}</td>
                        <td>{{ $trip->passengers }}</td>
                        <td>{{ $trip->origin }}</td>
                        <td></td> <!-- DISTRITO (no data) -->
                        <td>{{ $trip->destination }}</td>
                        <td>{{ $trip->payment_method == 'E' ? 'X' : '' }}</td>
                        <td>{{ $trip->payment_method == 'CH' ? 'X' : '' }}</td>
                        <td>${{ number_format($trip->amount, 2) }}</td>
                        <td>{{ $trip->driver->name ?? '' }}</td>
                        <td>{{ $trip->payment_method == 'TJ' ? 'X' : '' }}</td>
                        <td>{{ $trip->is_ida ? 'X' : '' }}</td>
                        <td>{{ $trip->is_vuelta ? 'X' : '' }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- Fill remaining rows to ensure table looks complete -->
                    @for($i = $trips->count() + 1; $i <= 20; $i++)
                    <tr>
                        <td class="row-number">{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <table class="footer-table">
                <tr>
                    <td>
                        <div class="footer-text">
                            E: EFECTIVO &nbsp;&nbsp; C.H.: CARGO HABITACION &nbsp;&nbsp; TJ: TARJETA
                        </div>
                    </td>
                    <td style="width: 260px;">
                        <div class="total-box">
                            <div class="total-label">TOTAL DE EFECTIVO $</div>
                            <div class="total-value">
                                ${{ number_format($trips->where('payment_method', 'E')->sum('amount'), 2) }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
