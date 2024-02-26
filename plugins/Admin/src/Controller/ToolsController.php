<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Cake\View\JsonView;

class ToolsController extends AdminAppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->loadComponent('FormProtection');
        $this->FormProtection->setConfig('validate', false);
    }

    public function doTmpFileUpload()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        // check if uploaded file is pdf
        $upload = $this->getRequest()->getData('upload');

        // non-pdf files will return false
        if (mime_content_type($upload->getStream()->getMetadata('uri')) != 'application/pdf') {
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', ['PDF']);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $extension = strtolower(pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION));
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('app.tmpUploadFilesDir') . DS . $filename;

        $upload->moveTo(WWW_ROOT . $filenameWithPath);

        $this->set([
            'status' => 1,
            'text' => __d('admin', 'Filename_General-terms-and-conditions') . '.pdf',
            'filename' => $filenameWithPath,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'text', 'filename']);
    }

    public function doTmpImageUpload()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        // check if uploaded file is image file
        $upload = $this->getRequest()->getData('upload');

        // non-image files will return false
        if (!in_array(mime_content_type($upload->getStream()->getMetadata('uri')), Configure::read('app.allowedImageMimeTypes'))) {
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', [join(', ', array_keys(Configure::read('app.allowedImageMimeTypes')))]);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $extension = strtolower(pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION));
        if ($extension == 'jpeg') {
            $extension = 'jpg';
        }
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('app.tmpUploadImagesDir') . DS . $filename;
        $upload->moveTo(WWW_ROOT . $filenameWithPath);

        $manager = new ImageManager(new Driver());
        $manager->read(WWW_ROOT . $filenameWithPath)
            ->scaleDown($this->getMaxTmpUploadFileSize())
            ->encodeByMediaType(quality: 100)
            ->save(WWW_ROOT . $filenameWithPath);

        $this->set([
            'status' => 1,
            'filename' => $filenameWithPath,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'filename']);
    }

    public function rotateImage()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        // check if uploaded file is image file
        $uploadedFile = $_SERVER['DOCUMENT_ROOT'] . $this->getRequest()->getData('filename');

        $direction = $this->getRequest()->getData('direction');

        $directionInDegrees = null;
        if ($direction == 'CW') {
            $directionInDegrees = 90;
        }
        if ($direction == 'ACW') {
            $directionInDegrees = -90;
        }
        if (is_null($directionInDegrees)) {
            $message = 'direction wrong';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $formatInfo = getimagesize($uploadedFile);

        // non-image files will return false
        if ($formatInfo === false || !in_array($formatInfo['mime'], Configure::read('app.allowedImageMimeTypes'))) {
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', [join(', ', array_keys(Configure::read('app.allowedImageMimeTypes')))]);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $manager = new ImageManager(new Driver());
        $manager->read($uploadedFile)
            ->rotate($directionInDegrees)
            ->encodeByMediaType(quality: 100)
            ->save($uploadedFile);

        $rotatedImageSrc = $this->getRequest()->getData('filename') . '?' . StringComponent::createRandomString(3);

        $this->set([
            'status' => 1,
            'rotatedImageSrc' => $rotatedImageSrc
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'rotatedImageSrc']);
    }

    /*
     * On uploading images are resized to fit to the maximum possibly required ...ImageSizes from app.config.php
     * return int
     */
    protected function getMaxTmpUploadFileSize()
    {

        $actTmpUploadFileSize = 0;
        $confKey = 'ImageSizes';

        // get all config keys "*ImageSizes"
        $imageSizes = Configure::read('app');
        foreach (array_keys($imageSizes) as $appKey) {
            if (strlen($appKey) < strlen($confKey) // prevent warnings of strrpos()
                || strrpos($appKey, $confKey, strlen($confKey) * (-1)) === false
                || !is_array($imageSizes[$appKey])
            ) {
                continue;
            }

            foreach ($imageSizes[$appKey] as $key => $value) {
                if ($key <= $actTmpUploadFileSize) {
                    continue;
                }
                $actTmpUploadFileSize = $key;
            }
        }

        return $actTmpUploadFileSize;
    }
}
