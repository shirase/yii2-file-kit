<?php

use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use shirase55\filekit\filesystem\FilesystemBuilderInterface;

/**
 * Class WebDAVFlysystemBuilder
 * @author Artem Dekhtyar <mail@artemd.ru>
 * @link https://github.com/shirase55/yii2-file-kit/issues/46
 */
class WebDAVFlysystemBuilder implements FilesystemBuilderInterface
{
    public $path;
    public $pathPrefix = '/extweb';

    public function build()
    {

        \yii\base\Event::on(\shirase55\filekit\Storage::className(), \shirase55\filekit\Storage::EVENT_BEFORE_SAVE, function ($event) {
            /** @var \shirase55\filekit\Storage $storage */
            $storage = $event->sender;

            if (!$storage->getFilesystem()->has('.dirindex')) {
                $storage->getFilesystem()->write('.dirindex', 1);
                $dirindex = 1;
            } else {
                $dirindex = $storage->getFilesystem()->read('.dirindex');
            }

            if($storage->maxDirFiles !== -1) {
                if($storage->getFilesystem()->has($dirindex)) {
                    $filesCount = count($storage->getFilesystem()->listContents($dirindex));
                    if ($filesCount > $storage->maxDirFiles) {
                        $dirindex++;
                        $storage->getFilesystem()->createDir($dirindex);
                    }
                } else {
                    $storage->getFilesystem()->createDir($dirindex);
                }
            }
        });

        $client = new \Sabre\DAV\Client([
            'baseUri' => 'https://webdav.yandex.ru',
        ]);
        $client->addCurlSetting(CURLOPT_SSL_VERIFYPEER, false);
        $client->addCurlSetting(CURLOPT_HTTPHEADER, [
            'Authorization: OAuth TOKENTOKENTOKEN', // https://tech.yandex.ru/disk/doc/dg/concepts/quickstart-docpage/
            'Accept: */*',
            'Host: webdav.yandex.ru'
        ]);

        $adapter = new WebDAVAdapter($client, '/');
        $flysystem = new Filesystem($adapter);

        if (!$flysystem->has($this->pathPrefix)) {
            $flysystem->createDir($this->pathPrefix);
        }
        $adapter->setPathPrefix($this->pathPrefix);

        return $flysystem;
    }
}