<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductImagesUploader\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Util\StringUtil;
use Plugin\ProductImagesUploader\Form\Type\Admin\ConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/product_images_uploader/config", name="product_images_uploader_admin_config")
     * @Template("@ProductImagesUploader/admin/config.twig")
     */
    public function index(Request $request, EventDispatcherInterface $dispatcher)
    {
        $form = $this->createForm(ConfigType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form['image_file']->getData();

            $fs = new Filesystem();
            $uniqId = sha1(StringUtil::random(32));
            $tmpDir = \sys_get_temp_dir().'/'.$uniqId;

            // 終了時に一時ディレクトリを削除.
            $dispatcher->addListener(KernelEvents::TERMINATE, function (PostResponseEvent $event) use ($tmpDir, $fs) {
                $fs->remove($tmpDir);
            });

            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath())) {
                $zip->extractTo($tmpDir);
                $zip->close();

                // zipファイル内にディレクトリがあればエラーにする
                $finder = new Finder();
                $count = $finder->in($tmpDir)->directories()->count();
                if ($count > 0) {
                    $this->addError('zipファイル内にディレクトリが含まれています。', 'admin');

                    return $this->redirectToRoute('product_images_uploader_admin_config');
                }

                // save_imageへコピー
                $fs->mirror($tmpDir, $this->eccubeConfig->get('eccube_save_image_dir'));

                $this->addSuccess('ファイルをアップロードしました。', 'admin');

                return $this->redirectToRoute('product_images_uploader_admin_config');
            } else {
                $this->addError('アップロードに失敗しました。', 'admin');

                return $this->redirectToRoute('product_images_uploader_admin_config');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
