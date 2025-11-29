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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            padding-top: 50px;
            margin: 0 auto;
            background-color: white;
        }

        /* Header Section */
        .header {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .logo-cell {
            width: 150px;
            border: 2px solid #333;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .info-cell {
            padding-left: 15px;
            vertical-align: top;
        }

        .company-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
            letter-spacing: 0.3px;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 30px;
            letter-spacing: 0.3px;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 13px;
        }

        .info-table {
            padding-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0 8px;
            border: none;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
            font-size: 12px;
        }

        .info-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 130px;
            text-align: center;
            font-size: 12px;
        }

        /* Table Section */
        .table-wrapper {
            margin: 5px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #333;
            font-size: 8px;
        }

        .data-table thead {
            background-color: #f0f0f0;
        }

        .data-table th {
            border: 1px solid #333;
            padding: 3px 2px;
            text-align: center;
            font-weight: bold;
            height: 20px;
            font-size: 7px;
            vertical-align: middle;
        }

        .data-table td {
            border: 1px solid #333;
            padding: 2px 1px;
            text-align: center;
            height: 18px;
            vertical-align: middle;
        }

        .row-number {
            background-color: #f9f9f9;
            font-weight: bold;
            width: 25px;
        }

        .text-left {
            text-align: left !important;
            padding-left: 4px !important;
        }

        /* Footer Section */
        .footer-section {
            margin-top: 10px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .footer-text {
            font-size: 9px;
            font-weight: bold;
        }

        .total-box {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            width: 220px;
            font-weight: bold;
            float: right;
        }

        .total-label {
            font-size: 11px;
            display: inline;
        }

        .total-value {
            border-bottom: none;
            font-size: 11px;
            display: inline;
            margin-left: 5px;
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
                        <img src="<?php echo e(public_path('shalom-logo.png')); ?>" alt="Shalom Logo" style="max-width: 130px; max-height: 80px;">
                    </td>
                    <td class="info-cell">
                        <div class="company-title">TRANSPORTES EJECUTIVOS SHALOM S.A. DE C.V.</div>
                        <div class="report-title">REPORTE DE BITACORA DIARIA HOTELES MARRIOTT</div>
                        
                        <table class="info-table">
                            <tr>
                                <td>
                                    <span class="info-label">TURNO:</span>
                                    <span class="info-line"><?php echo e($shift ?: '&nbsp;'); ?></span>
                                </td>
                                <td>
                                    <span class="info-label">COORDINADOR:</span>
                                    <span class="info-line"><?php echo e($coordinator ?: '&nbsp;'); ?></span>
                                </td>
                                <td>
                                    <span class="info-label">FECHA:</span>
                                    <span class="info-line"><?php echo e($date); ?></span>
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
                        <th style="width: 40px;">NO. PASAJEROS</th>
                        <th>SALIDA</th>
                        <th>DESTINO</th>
                        <th style="width: 30px;">E</th>
                        <th style="width: 30px;">C.H</th>
                        <th style="width: 30px;">TJ</th>
                        <th style="width: 60px;">MONTO</th>
                        <th>CONDUCTOR</th>
                        <th style="width: 30px;">EQ.</th>
                        <th style="width: 30px;">IDA</th>
                        <th style="width: 30px;">VTA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="row-number"><?php echo e($index + 1); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($trip->trip_time)->format('H:i')); ?></td>
                        <td><?php echo e($trip->passengers); ?></td>
                        <td class="text-center"><?php echo e($trip->origin); ?></td>
                        <td class="text-center"><?php echo e($trip->destination); ?></td>
                        <td><?php echo e($trip->payment_method == 'E' ? 'X' : ''); ?></td>
                        <td><?php echo e($trip->payment_method == 'CH' ? 'X' : ''); ?></td>
                         <td><?php echo e($trip->payment_method == 'TJ' ? 'X' : ''); ?></td>
                        <td>$<?php echo e(number_format($trip->amount, 2)); ?></td>
                        <td class="text-center"><?php echo e($trip->driver->name ?? ''); ?></td>
                        <td class="text-center"><?php echo e($trip->equipment_number ?? ''); ?></td>
                        <td><?php echo e($trip->is_ida ? 'X' : ''); ?></td>
                        <td><?php echo e($trip->is_vuelta ? 'X' : ''); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <!-- Fill remaining rows to ensure table looks complete -->
                    <?php for($i = $trips->count() + 1; $i <= 20; $i++): ?>
                    <tr>
                        <td class="row-number"><?php echo e($i); ?></td>
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
                    <?php endfor; ?>
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
                            <span class="total-label">TOTAL DE EFECTIVO $</span>
                            <span class="total-value">
                                $<?php echo e(number_format($trips->where('payment_method', 'E')->sum('amount'), 2)); ?>

                            </span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\cuent\OneDrive\Escritorio\Projects\logbook-app\resources\views/reports/trips-report.blade.php ENDPATH**/ ?>