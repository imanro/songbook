<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 28.06.17
 * Time: 13:30
 */


namespace Songbook\Model\Cloud\Driver;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_FileList;
use Songbook\Model\Cloud;
use Songbook\Model\Cloud\CloudFile;
use Zend\Http\Request;
use Zend\Session\Container;

class GDrive implements DriverInterface
{
    /**
     * @var Cloud
     */
    protected $cloud;

    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * @var Google_Service_Drive
     */
    protected $service;

    /**
     * @var string
     */
    protected $rootFolderName;

    /**
     * @var \Songbook\Service\Setting
     */
    protected $settingService;

    /**
     * @var \User\Service\User
     */
    protected $userService;


    protected function getCloud()
    {
        return $this->cloud;
    }

    public function setCloud(Cloud $cloud)
    {
        $this->cloud = $cloud;
        $this->init();
        return $this;
    }

    /**
     * @return string
     */
    protected function getRootFolderName()
    {
        return $this->rootFolderName;
    }

    /**
     * @param string $rootFolderName
     * @return GDrive
     */
    public function setRootFolderName($rootFolderName)
    {
        $this->rootFolderName = $rootFolderName;
        return $this;
    }

    protected function getService()
    {
        return $this->service;
    }

    protected function setService(Google_Service_Drive $service)
    {
        $this->service = $service;
    }

    protected function getClient()
    {
        return $this->client;
    }

    protected function setClient(Google_Client $client)
    {
        $this->client = $client;
        return $this;
    }

    protected function init()
    {
        $this->initClient();
        if(is_null($this->client)) {
            throw new \Songbook\Model\Cloud\Driver\Exception('Unable to init driver', \Songbook\Model\Cloud\Driver\Exception::CODE_UNABLE_TO_INIT_DRIVER);
        }

        $this->initService();
        $this->setRootFolderName($this->getSettingService()->getValue($this->getUserService()->getCurrentUser(), \Songbook\Entity\Setting::VAR_GDRIVE_ROOT_FOLDER_NAME));

        return $this;
    }

    protected function initClient()
    {
        $client = new Google_Client();

        $config = $this->getCloud()->getServiceLocator()->get('Config')['cloud']['gdrive'];
        $client->setApplicationName($config['application_name']);
        $client->setAuthConfig($config['auth_config_path']);
        $client->setIncludeGrantedScopes(true);   // incremental auth

        // If modifying these scopes, delete your previously saved credentials
        $client->addScope(Google_Service_Drive::DRIVE);

        //$client->setScopes(implode(' ', array(Google_Service_Drive::DRIVE_METADATA_READONLY)));

        $config = $this->getCloud()->getServiceLocator()->get('Config')['cloud']['gdrive'];

        if ($config['is_auth_offline']) {
            $this->offlineAuthenticate($client);
        } else {
            $this->onlineAuthenticate($client);
        }

        $this->setClient($client);
    }

    protected function initService()
    {
        $this->setService(new Google_Service_Drive($this->getClient()));
    }

    protected function onlineAuthenticate(Google_Client $client)
    {
        $config = $this->getCloud()->getServiceLocator()->get('Config')['cloud']['gdrive'];

        //$container->access_token = 'test';
        $container = new Container($config['session_ns']);
        if (isset($container->gdrive_access_token)
            && is_array($container->gdrive_access_token)
        ) {

            $client->setAccessToken($container->gdrive_access_token);
            if (!$client->isAccessTokenExpired()) {
                ;
            } else {
                $this->redirectToOnlineAuthentication($client);
            }
        } else {
            $this->redirectToOnlineAuthentication($client);
        }
    }

    protected function redirectToOnlineAuthentication(Google_Client $client)
    {
        $config = $this->getCloud()->getServiceLocator()->get('Config')['cloud']['gdrive'];
        $container = new Container($config['session_ns']);
        $redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . $config['redirect_path'];
        $client->setRedirectUri($redirectUri);
        $application = $this->getCloud()->getServiceLocator()->get('Application');
        $uri = $application->getRequest()->getUriString();
        $container->gdrive_return_uri = $uri;
        header('Location: ' . filter_var($redirectUri, FILTER_SANITIZE_URL));
    }

    protected function offlineAuthenticate(Google_Client $client)
    {
        $client->setAccessType('offline');

        $config = $this->getCloud()->getServiceLocator()->get('Config')['cloud']['gdrive'];

        if(file_exists($config['offline_token_path'])){
            $accessToken = json_decode(file_get_contents($config['offline_token_path']), true);
            $client->setAccessToken($accessToken);

        } else {

            $request = $this->getCloud()->getServiceLocator()->get('Application')->getRequest();
            /* @var $request Request */

            $code = $request->getQuery('code');

            if(!empty($code)){
                $accessToken = $client->fetchAccessTokenWithAuthCode($code);
                file_put_contents($config['offline_token_path'], json_encode($accessToken));
                $client->setAccessToken($accessToken);

            } else {
                $authUrl = $client->createAuthUrl();
                die('Open this url and append ?code=_code_ to query string of this page, replacing _code_ with code that you will receive<br/><a href="' . $authUrl . '" target="_blank">' . $authUrl . '</a>');
            }
        }

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($config['offline_token_path'], json_encode($client->getAccessToken()));
        }
    }

    public function oauth2Authenticate($code = null)
    {
        // TODO: Implement oauth2Authenticate() method.
        $client = $this->getClient();

        if (empty($code)) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

        } else {
            $client->fetchAccessTokenWithAuthCode($code);

            $config = $this->getCloud()->getServiceLocator()->get('Config');
            $container = new Container($config['session_ns']);

            $container->gdrive_access_token = $client->getAccessToken();

            // todo: change it
            $returnUri = $container->gdrive_return_uri;

            if(empty($returnUri)){
                $returnUri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
            }

            header('Location: ' . filter_var($returnUri, FILTER_SANITIZE_URL));
        }
    }

    public function getFile($name)
    {
        // TODO
        $client = $this->getClient();
    }

    public function downloadFile($fileId)
    {
        $service = $this->getService();

        $file = $service->files->get($fileId);

        if(strpos($file->mimeType, 'application/vnd.google-apps') !== false){
            $response = $service->files->export($fileId, $this->getMimeSubstitute($file->mimeType), array(
                'alt' => 'media'));
        } else {
            $response = $service->files->get($fileId, array(
                'alt' => 'media'));
        }

        return $response->getBody()->getContents();
    }

    public function getFiles($parentFolder)
    {
        $service = $this->getService();

        $parameters = [];
        $parameters['q'] = "'" . $parentFolder->getId() . "' in parents and trashed=false";

        $files = $service->files->listFiles($parameters);
//var_dump($files);
        return $this->initCloudFiles($files);
    }

    public function uploadFile($parentFolder, $fsPath, $name)
    {
        if(is_null($name)){
            $name = pathinfo($fsPath, \PATHINFO_FILENAME);
        }

        $cloud = $this->getCloud();
        $mime = $cloud->getFileMimeType($fsPath);

        $service = $this->getService();

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'parents' => [$parentFolder->getId()],
            'name' => $name));

        $file = $service->files->create($fileMetadata, array(
            'data' => file_get_contents($fsPath),
            'mimeType' => $mime,
            'uploadType' => 'multipart',
            'fields' => 'id'));

        return $file->getId();
    }

    public function getFolder($name)
    {
        $service = $this->getService();

        $rootFolder = $this->getRootFolder();

        $parameters = [];
        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and '" . $rootFolder->getId() . "' in parents and name='". $name ."' and trashed=false";

        $result = $service->files->listFiles($parameters);

        if( $result->count() > 0){
            return $result[0];
        } else {
            return $this->createFolder($name, $rootFolder);
        }
    }

    protected function getRootFolder()
    {
        $service = $this->getService();
        $name = $this->getRootFolderName();

        $parameters = [];
        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and 'root' in parents and name='". $name ."' and trashed=false";

        $result = $service->files->listFiles($parameters);
        if( $result->count() > 0){
            return $result[0];
        } else {
            return $this->createRootFolder();
        }
    }

    protected function createRootFolder()
    {
        $service = $this->getService();
        $name = $this->getRootFolderName();

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder'
            ));

        $item = $service->files->create($fileMetadata);

        if(is_null($item)){
            throw new \Songbook\Model\Cloud\Driver\Exception('Unable to create root folder', \Songbook\Model\Cloud\Driver\Exception::CODE_UNABLE_TO_CREATE_ROOT_FOLDER);
        }

        return $item;
    }

    protected function createFolder($name, Google_Service_Drive_DriveFile $parent)
    {
        $service = $this->getService();

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($parent->getId())
        ));

        $item = $service->files->create($fileMetadata);

        if(is_null($item)){
            throw new \Songbook\Model\Cloud\Driver\Exception('Unable to create folder', \Songbook\Model\Cloud\Driver\Exception::CODE_UNABLE_TO_CREATE_FOLDER);
        }

        return $item;
    }

    /**
     * @return \Songbook\Service\Setting
     */
    protected function getSettingService ()
    {
        if (is_null($this->settingService)){
            $sm = $this->getCloud()->getServiceLocator();
            $this->settingService = $sm->get('Songbook\Service\Setting');
        }

        return $this->settingService;
    }

    /**
     * @return \User\Service\User
     */
    protected function getUserService ()
    {
        if (! $this->userService) {
            $sm = $this->getCloud()->getServiceLocator();
            $this->userService = $sm->get('User\Service\User');
        }

        return $this->userService;
    }

    protected function initCloudFile($id, $name, $mimeType)
    {
        $file = new CloudFile();
        $file->setId($id);
        $file->setName($name);
        $file->setMimeType($mimeType);
        return $file;
    }

    protected function initCloudFiles(Google_Service_Drive_FileList $files)
    {
        $cloudFiles = array();
        foreach ($files as $file) {
            /* @var $file Google_Service_Drive_DriveFile */
            $cloudFiles []= $this->initCloudFile($file->getId(), $file->getName(), $file->getMimeType());
        }

        return $cloudFiles;
    }

    protected function getMimeSubstitute($googleMimeType)
    {
        $list = array(
            'application/vnd.google-apps.document' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.google-apps.presentation' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        );

        if(isset($list[$googleMimeType])){
            return $list[$googleMimeType];
        } else {
            throw new \Songbook\Model\Cloud\Driver\Exception('Unable to find appropritate mime type for this file', \Songbook\Model\Cloud\Driver\Exception::CODE_UNABLE_TO_FIND_MIME_TYPE);
        }
    }
}