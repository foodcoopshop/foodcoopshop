<?php
/**
 * ToolsController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ToolsController extends AdminAppController
{

    public function doTmpImageUpload()
    {
        $this->autoRender = false;
        
        // check if uploaded file is image file
        $formatInfo = getimagesize($this->params['form']['upload']['tmp_name']);
        // non-image files will return false
        if ($formatInfo === false || $formatInfo['mime'] != 'image/jpeg') {
            $message = 'Die hochgeladene Datei muss im Format "jpg" sein.';
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }
        
        $extension = strtolower(pathinfo($this->params['form']['upload']['name'], PATHINFO_EXTENSION));
        if ($extension == 'jpeg')
            $extension = 'jpg';
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('app.tmpUploadImagesDir') . DS . $filename;
        $thumb = PhpThumbFactory::create($this->params['form']['upload']['tmp_name']);
        $thumb->resize(Configure::read('app.tmpUploadFileSize'));
        $thumb->save(WWW_ROOT . $filenameWithPath);
        
        die(json_encode(array(
            'status' => 1,
            'filename' => $filenameWithPath
        )));
    }

    public function rotateImage()
    {
        $this->autoRender = false;
        
        // check if uploaded file is image file
        $uploadedFile = $_SERVER['DOCUMENT_ROOT'] . $this->params['data']['filename'];
        
        $direction = $this->params['data']['direction'];
        $formatInfo = getimagesize($uploadedFile);
        
        // non-image files will return false
        if ($formatInfo === false || $formatInfo['mime'] != 'image/jpeg') {
            $message = 'Die hochgeladene Datei muss im Format "jpg" sein.';
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }
        
        $thumb = PhpThumbFactory::create($uploadedFile);
        $thumb->rotateImage($direction);
        $thumb->save($uploadedFile);
        
        $rotatedImageSrc = $this->params['data']['filename'] . '?' . StringComponent::createRandomString(3);
        die(json_encode(array(
            'status' => 1,
            'rotatedImageSrc' => $rotatedImageSrc
        )));
    }

    public function ajaxCancelFormPage()
    {
        Configure::write('debug', 0);
        $this->autoRender = false;
        
        $referer = $this->params['data']['referer'];
        if ($referer == '')
            $referer = '/';
        
        $objectClass = $this->params['data']['objectClass'];
        $id = $this->params['data']['id'];
        
        $this->loadModel($objectClass);
        
        $object = $this->$objectClass->find('first', array(
            'conditions' => array(
                $objectClass . '.' . $this->$objectClass->primaryKey => $id
            )
        ));
        
        // eigene bearbeitungs-hinweise bei click auf cancel löschen
        // if ($object[$objectClass]['currently_updated_by'] == $this->AppAuth->getUserId()) {
        // $this->$objectClass->id = $id;
        // $this->$objectClass->saveField('currently_updated_by', 0);
        // }
        
        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok',
            'referer' => $referer
        )));
    }
}

?>