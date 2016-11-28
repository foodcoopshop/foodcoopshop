<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

App::uses('FCS_TCPDF', 'Lib/Pdf');
$pdf = new FCS_TCPDF();
$pdf->SetLeftMargin(12);
$pdf->SetRightMargin(12);

$title = 'Informationen über Rücktrittsrecht';
if (isset($order)) {
    $title .= ' und Rücktrittsformular';
}
$pdf->SetTitle($title);
$pdf->infoTextForFooter = $title;

$pdf->AddPage();

$html = $this->element('legal/cancellationTerms');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->infoTextForFooter = 'Information über Rücktrittsrecht';

if (!empty($manufacturers)) {
    
    foreach($manufacturers as $manufacturer) {
        $i = 0;
        
        foreach($manufacturer as $product) {
            
            if ($i > 0) continue;
            
            $pdf->AddPage();
            
            $pdf->infoTextForFooter = 'Rücktrittsformular ' . $product['Manufacturer']['name'];
            
            $html = '<h1>Rücktrittsformular</h1>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(8);
            
            $html = '<p>Wenn Sie den Vertrag widerrufen wollen, dann füllen Sie bitte dieses Formular aus und senden Sie es zurück.</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(8);
            
            $html = $this->Html->getManufacturerImprint($product['Manufacturer'], 'pdf', true);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(4);
            
            $html = '<p>Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag über den Kauf der folgenden Waren (*)/die Erbringung der folgenden Dienstleistung (*)</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);
            
            $html = '<p>____________________________________________________________________________________________</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);
            
            $html = '<p>Bestellt am (*): '.$this->Time->formatToDateNTimeLong($order['Order']['date_add']).'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            
            $html = '<p>Erhalten am (*): </p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);
            
            $html = '<p>Name des/der Verbraucher(s): '.$appAuth->getUsername().'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            
            $customerAddress = $appAuth->user('AddressCustomer.address1');
            if ($appAuth->user('AddressCustomer.address2') != '') {
                $customerAddress .= ', '.$appAuth->user('AddressCustomer.address2');
            }
            $customerAddress .= $appAuth->user('AddressCustomer.postcode') . ' ' . $appAuth->user('AddressCustomer.city');
            $html = '<p>Anschrift des/der Verbraucher(s): '.$customerAddress.'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);
            
            $html = '<p>Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier)</p><br /><br />';
            $html .= '<p>___________________________________________________________</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);
            
            $html = '<p>Datum: ______________________</p>';
            $pdf->Ln(8);
            
            $html .= '<p>(*) Unzutreffendes streichen</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            
            $i++;
            
        }
        
    }
    
}

$filename = @$this->Html->getCancellationInformationAndFormPDFLink($order);

if (file_exists($filename))
    unlink($filename);

    App::uses('Folder', 'Utility');
    $dir = new Folder();
    $path = dirname($filename);
    $dir->create($path);
    $dir->chmod($path, 0755);
    
if ($saveParam == 'I') {
    $filename = str_replace(Configure::read('app.folder.orders_with_current_year_and_month'), '', $filename);
}
echo $pdf->Output($filename, $saveParam);

?>