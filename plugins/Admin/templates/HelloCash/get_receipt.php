<?php
declare(strict_types=1);

use Cake\Core\Configure;

$isCancellationInvoice = isset($helloCashInvoice->invoice_cancellation) && $helloCashInvoice->invoice_cancellation == 1;

?>
<!doctype html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/favicons/favicon.ico" type="image/x-icon" />
    <title>Rechnung Nr.: <?php echo $helloCashInvoice->invoice_number; ?></title>
    <style>
        @media print {
            .no-print, .no-print * {
                display: none !important;
            }
            .rePanel {
                border: 0 solid #fff;
                box-sizing: border-box;
                box-shadow: none !important;
            }
        }
        body {
            margin: 0;
            padding: 0 0 30px 0;
            border: solid 0 #fff;
            background-color: #f8fafb;
        }
        body, p, div, table {
            font-family: sans-serif;
            font-size: 3.1mm;
        }
        .rePanel {
            width: 70mm;
            padding: 4mm;
            margin: 30px auto 0 auto;
            text-align: center;
            box-sizing: border-box;
            background-color: #fff;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px, rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;
        }
        .logoTop  {
            width: 70mm;
            margin: 30px auto 0 auto;
            text-align: center;
        }
        @media print {
            .logoTop {
                display: none;
            }
        }
        .rePanel h1 {
            font-size: 8mm;
            margin: 1mm 0 2.5mm 0;
            padding: 0 0 2.5mm 0;
            text-align: center;
            width: 100%;
            border-bottom: dashed 0.4mm #000;
        }
                .posTd0 {
            text-align: center;
        }
        .posTd1 {
            text-align: left;
        }
        .posTd2, .posTd3 {
            text-align: right;
        }
        .posTd1 {
            line-height: 3.1mm;
            -moz-hyphens: auto;
            -o-hyphens: auto;
            -webkit-hyphens: auto;
            -ms-hyphens: auto;
            hyphens: auto;
            font-size: 2.5mm;
        }
                .divDotted {
            margin-bottom: 2.5mm;
            padding-bottom: 2.5mm;
            border-bottom: dashed 0.4mm #000;
        }
        .divDottedTop {
            margin-top: 2.5mm;
            padding-top: 2.5mm;
            border-top: dashed 0.4mm #000;
        }
        .bold {
            font-weight: bold;
        }
        .testMode {
            font-size: 5mm;
            border-top: 0.4mm solid #000;
            border-bottom: 0.4mm solid #000;
            margin: 1.3mm;
            padding: 1.3mm;
        }
        .solidLine {
            font-size: 5mm;
            border-bottom: 0.4mm solid #000;
            margin: 1.3mm;
            padding: 1.3mm;
        }

        .logo {
            margin: 0 auto;
        }
        table {
            display: table;
            border-collapse: separate;
        }
        table td {
            display: table-cell;
        }
        .break,
        table td {
            -ms-word-break: break-all;
            word-break: break-all;
            word-break: break-word;
        }
        .table-fixed {
            width: 100%;
        }
        .arrow {
            display: inline-block;
            font-size: 16px;
            width: 10px;
            height: 16px;
            margin-top: -10px;
        }
        .smallTd {
            text-align:center;
            font-size:2.7mm;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .w-100 table {
            width: 100%;
        }
        .hospitality-receipt table td {
            padding-top: 4mm;
            width: 100%;
            text-align: left;
        }
        .hospitality-receipt table.footer {
            padding-left: 0;
            padding-right: 0;
            margin-top: 5mm;
        }
        .hospitality-receipt table.footer td {
            padding-top: 4mm;
            width: 50%;
            text-align: center;
            padding-left: 0;
            padding-right: 0;
        }
        .order-query-block td {
            line-height: 2mm;
            font-size: 2mm;
        }
        #btn-print {
            width: 280px;
            height: 72px;
            padding: 6px 6px;
            border-radius: 3px;
            background-color: #039ED9;
            color: white;
            border: none;
            line-height: 22px;
            letter-spacing: 0em;
            text-align: center;
            margin-top: 12px;
            margin-bottom: 8px;
        }
        .button-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .button-container p {
            font-family: 'Open Sans', sans-serif;
            font-size: 4mm;
            font-weight: 600;
        }
        .center-button {
            display: flex;
            justify-content: center;
            position: fixed;
            bottom: 0;
            left: 0; /* added */
            width: 100%;
            padding: 1mm;
            box-sizing: border-box;
            background-color: white;
        }

        </style>

        <script type="text/javascript">
            function print_frame() {
                window.print ? window.print() : document.execCommand('print', false, null)
            }

            function addEvent(event, elem, func) {
                if (elem.addEventListener)
                    elem.addEventListener(event, func, false);
                else if (elem.attachEvent) {
                    elem.attachEvent('on' + event, func);
                } else {
                    elem['on' + event] = func;
                }
            }

            addEvent('DOMContentLoaded', document, function () {
                var btnPrint = document.getElementById('btn-print');

                if (btnPrint !== null) {
                    addEvent('click', btnPrint, print_frame);
                }
            });
        </script>

        <script type="text/javascript">
            window.addEventListener('load', print_frame);
        </script>

    </head>

<body>
    <div class="rePanel" data-testid="bon-receipt">
        <div class="break">
            <?php
                $logoReceipt = '/files/images/logo-receipt.png';
                if (file_exists(WWW_ROOT . $logoReceipt)) {
                    $logoReceipt = $logoReceipt . '?' . filemtime(WWW_ROOT . $logoReceipt);
                    echo "<img style='width:100%' class='logo' alt='Logo' src='" . $logoReceipt . "'>";
                }
            ?>
            <h1 style="font-family: bonFont;font-size:3mm"><?php echo $helloCashInvoice->company->name; ?> </h1>
        </div>

        <div class="divDotted break">
            <?php echo $helloCashInvoice->company->street . ' ' . $helloCashInvoice->company->houseNumber; ?><br /><?php echo $helloCashInvoice->company->postalCode; ?> <?php echo $helloCashInvoice->company->city; ?>
            <br />
            <?php
            if (isset($helloCashInvoice->company->companyRegister)) {
                echo 'UID: ' . $helloCashInvoice->company->companyRegister . '<br />';
            }
            if (isset($helloCashInvoice->company->phoneNumber)) {
                echo 'Tel.:' . $helloCashInvoice->company->phoneNumber . '<br />';
            }
            if (isset($helloCashInvoice->company->email)) {
                echo 'E-Mail: ' . $helloCashInvoice->company->email . '<br />';
            }
            if (isset($helloCashInvoice->company->iban)) {
                echo 'IBAN: ' . $helloCashInvoice->company->iban . '<br />';
            }
            if (isset($helloCashInvoice->company->website)) {
                echo 'Web: ' . $helloCashInvoice->company->website . '<br />';
            }
            ?>
        </div>

        <div class="divDotted break">
            <div class="bold">Kunde<br />
                <?php
                    echo $invoice->customer->name;
                    if ($invoice->customer->is_company && $invoice->customer->lastname != '') {
                        echo ', ' . $invoice->customer->lastname;
                    }
                ?>
            </div>

            <div class="bold">
                <?php echo $helloCashInvoice->customer->customer_street . ', ' . $helloCashInvoice->customer->customer_postalCode . ' ' . $helloCashInvoice->customer->customer_city; ?>
            </div>
        </div>
        
        <?php /* START CANCELLATION INVOICES */ ?>
        <?php if ($isCancellationInvoice) { ?>
            <div class="divDotted break">
                <h2>Storno</h2>
                <div style="margin:1mm;">
                    Beleg Nr.:
                    <b><?php echo $helloCashInvoice->invoice_number; ?></b> &nbsp;
                    vom:
                    <b><?php echo date(Configure::read('DateFormat.DateNTimeShortWithSecsAlt'), strtotime($helloCashInvoice->invoice_timestamp)); ?></b>
                </div>
            </div>

            <div class="divDotted break">
                <b>Storno Beleg Nr.: <?php echo $helloCashInvoice->cancellation_details->cancellation_number; ?></b>
                <div style="margin:1mm;">Erstellt von: <?php echo $helloCashInvoice->cancellation_details->cancellation_cashier; ?></div>
                <div style="margin:1mm;">Am: 16.02.2024 13:53:55</div>
            </div>
            <?php /* END CANCELLATION INVOICES */ ?>
 
        <?php } else { ?>

        <?php /* START NORMAL INVOICES */ ?>
            <div class="divDotted">
                <table cellpadding="2" cellspacing="0" class="table-fixed">
                    <tr>
                        <td width="67%" align="left">Kassier: <?php echo $helloCashInvoice->invoice_cashier; ?></td>
                        <td width="33%" align="left">Beleg Nr.: <?php echo $helloCashInvoice->invoice_number; ?></td>
                    </tr>
                    <tr>
                        <td align="left">Dat.: <?php echo date(Configure::read('DateFormat.DateNTimeShortWithSecsAlt'), strtotime($helloCashInvoice->invoice_timestamp)); ?></td>
                        <td align="left">Kassa: 1</td>
                    </tr>
                </table>
            </div>
        <?php } ?>
        <?php /* END NORMAL INVOICES */ ?>
        
        <div class="divDotted">
            <table class="table-fixed" cellspacing="4" cellpadding="0">
                <tr>
                    <td style="width:15%" align="center"><b>Anz</b></td>
                    <td style="width:35%" align="left"><b>Artikel/DL</b></td>
                    <td style="width:25%" align="right"><b>E-Preis</b></td>
                    <td style="width:25%" align="right"><b>G-Preis</b></td>
                </tr>
                <?php foreach($helloCashInvoice->items as $item) { ?>

                    <?php
                        if ($isCancellationInvoice) {
                            $item->item_quantity *= -1;
                            $item->item_total *= -1;
                        }
                    ?>

                    <tr>
                        <td class="posTd0"><?php echo $this->Number->formatAsDecimal($item->item_quantity, 0); ?></td>
                        <td class="posTd1"><?php echo $item->item_name; ?></td>
                        <td class="posTd2"><?php echo $this->Number->formatAsDecimal($item->item_price); ?></td>
                        <td class="posTd3"><?php echo $this->Number->formatAsDecimal($item->item_total); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="divDotted">
            
            <table style="width:100%">
                <tr>
                    <td class="text-left" ><b>Summe:</b></td>
                    <td></td>
                    <?php
                        if ($isCancellationInvoice) {
                            $helloCashInvoice->invoice_total *= -1;
                        }
                    ?>
                    <td data-testid="total" class="text-right"><b><?php echo $this->Number->formatAsCurrency($helloCashInvoice->invoice_total); ?></b></td>
                </tr>
            </table>
        </div>

        <div class="divDotted">
            <table cellspacing="4" cellpadding="0" class="table-fixed">
                <tr>
                    <td width='25%'>
                        <b>USt %</b>
                    </td>
                    <td width='25%'>
                        <b>Netto €</b>
                    </td>
                    <td width='25%'>
                        <b>Steuer €</b>
                    </td>
                    <td width='25%'>
                        <b>Brutto €</b>
                    </td>
                </tr>

                <?php $helloCashInvoice->taxes = array_reverse($helloCashInvoice->taxes); ?>
                <?php foreach($helloCashInvoice->taxes as $tax) { ?>

                    <?php
                    if ($isCancellationInvoice) {
                        $tax->tax_net *= -1;
                        $tax->tax_tax *= -1;
                        $tax->tax_gross *= -1;
                    }
                    ?>

                    <tr>
                        <td><?php echo $this->Number->formatAsDecimal($tax->tax_taxRate, 0); ?></td>
                        <td><?php echo $this->Number->formatAsDecimal($tax->tax_net, 2); ?></td>
                        <td><?php echo $this->Number->formatAsDecimal($tax->tax_tax, 2); ?></td>
                        <td><?php echo $this->Number->formatAsDecimal($tax->tax_gross, 2); ?></td>
                    </tr>
                    <?php } ?>
            </table>
        </div>
        
        <div class="divDotted break">
            Zahlungsart: <?php echo $helloCashInvoice->invoice_payment; ?><br/>
            <?php if (!$isCancellationInvoice) { ?>
                Bezahlt: <?php echo $this->Number->formatAsCurrency($helloCashInvoice->invoice_total); ?>
            <?php } ?>
            <?php if ($helloCashInvoice->invoice_payment == 'Guthaben-System') { ?>
                <br /><b>Der Betrag wurde von deinem Guthaben abgezogen.</b>
            <?php } ?>
        </div>

        <div class="break">
            <p>Vielen Dank f&uuml;r deinen Einkauf! Rechnungsdatum = Lieferdatum
            <?php
                if (Configure::check('app.additionalReceiptText')) {
                    echo '<br />' . Configure::read('app.additionalReceiptText');
                }
            ?>
            </p>

        </div>

        <span style="font-size: 2.5mm">
            <?php
            if (!empty($helloCashInvoice->signature)) {
                if (!$isCancellationInvoice) {
                    $signatureCode = $helloCashInvoice->signature->signature_code;
                } else {
                    $signatureCode = $helloCashInvoice->signature->signature_cancellation_code;
                }
                $barcodeobj = new TCPDF2DBarcode($signatureCode, 'QRCODE,L');
                $imgBase64Encoded = base64_encode($barcodeobj->getBarcodePngData(6, 6, [0,0,0]));
                echo '<img alt="QR-Code" style="padding:10px;width:110px" src="data:image/png;base64,' . $imgBase64Encoded . '">';
            
                if (!$isCancellationInvoice) {
                    $signatureText = $helloCashInvoice->signature->signature_text;
                } else {
                    $signatureText = $helloCashInvoice->signature->signature_cancellation_text;
                }
                $signatureText = str_replace('RKSV number:', 'RKSV Nummer:', $signatureText);
                $signatureText = str_replace('Cash register ID:', 'Kassen-ID:', $signatureText);
                echo '<br />' . $signatureText;
            }
            ?>
        </span>

        <span style="font-size: 2.5mm"></span>

    </div>
</body>
</html>
