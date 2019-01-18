<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;
use Cake\I18n\I18n;

class Fontawesome5 extends AbstractMigration
{
    public function change()
    {

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [
            'conditions' => [
                'ActionLogs.type' => 'cronjob_send_invoices'
            ]
        ]);
        
        $regex = '/\<ul class="jquery-ui-icon"\>\<li class="ui-state-default ui-corner-all"\>\<a href="(.*)" target="_blank"\>\<img src="\/node_modules\/famfamfam-silk\/dist\/png\/arrow_right.png\?(.*)" alt=""\/\>\<\/a\>\<\/li\>\<\/ul\>/siU';
        $replacement = '<a href="$1" class="btn btn-outline-light" target="_blank"><i class="fas fa-arrow-right ok"></i></a>';
        foreach($actionLogs as $actionLog) {
            $newActionLogText = preg_replace($regex, $replacement, $actionLog->text);
            $data2save = [
                'text' => $newActionLogText
            ];
            $this->ActionLog->save(
                $this->ActionLog->patchEntity(
                    $actionLog,
                    $data2save
                )
            );
        }
                
        $this->execute('UPDATE fcs_configuration SET text = "Additional text that is sent in the registration e-mail after a successful registration. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-mail preview</a>" WHERE name = "FCS_REGISTRATION_EMAIL_TEXT" AND locale = "en_US";');
        $this->execute('UPDATE fcs_configuration SET text = "Zusätzlicher Text, der in der Bestätigungsmail nach einer Registrierung versendet wird. <br /> <a href=\"/admin/configurations/previewEmail/FCS_REGISTRATION_EMAIL_TEXT\" target=\"_blank\"><i class=\"fas fa-info-circle\"></i> E-Mail-Vorschau anzeigen</a>" WHERE name = "FCS_REGISTRATION_EMAIL_TEXT" AND locale = "de_DE";');
        
    }
}
