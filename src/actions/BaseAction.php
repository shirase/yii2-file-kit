<?php
namespace shirase55\filekit\actions;

use shirase55\filekit\Storage;
use yii\base\Action;
use yii\di\Instance;

/**
 * Class BaseAction
 * @package shirase55\filekit\actions
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class BaseAction extends Action
{
    /**
     * @var string file storage component name
     */
    public $fileStorage = 'fileStorage';
    /**
     * @var string Request param name that provides file storage component name
     */
    public $fileStorageParam = 'fileStorage';
    /**
     * @var string session key to store list of uploaded files
     */
    public $sessionKey = '_uploadedFiles';
    /**
     * Allows users to change filestorage by passing GET variable
     * @var bool
     */
    public $allowChangeFilestorage = false;

    /**
     * @return \shirase55\filekit\Storage
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFileStorage()
    {
        if ($this->allowChangeFilestorage) {
            $fileStorage = \Yii::$app->request->get($this->fileStorageParam);
        } else {
            $fileStorage = $this->fileStorage;
        }
        $fileStorage = Instance::ensure($fileStorage, Storage::className());
        return $fileStorage;
    }
}
