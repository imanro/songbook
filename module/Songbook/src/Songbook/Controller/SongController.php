<?php
namespace Songbook\Controller;
use Songbook\Entity\Content;
use Songbook\Form\SongEditForm;
use Songbook\Entity\Song;
use Songbook\Service;
use Zend\View\Model\ViewModel;

/**
 * @author manro
 */
class SongController extends FrontendController
{
    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Content
     */
    protected $contentService;

    /**
     * @var \User\Service\User
     */
    protected $userService;

    /**
     * \Doctrine\ORM\EntityManager
     */
    protected $em;


    public function indexAction ()
    {
        return new ViewModel(
                array(
                    'songs' => $this->getEntityManager()
                        ->getRepository('Songbook\Entity\Song')
                        ->findAll()
                ));
    }

    public function addAction ()
    {
        return $this->forward()->dispatch('Song', array(
            'action' => 'edit',
            'id' => $this->params('id', null)
        ));
    }

    public function editAction ()
    {
        $em = $this->getEntityManager();
        $id = $this->params('id', null);


        if (! is_null($id)) {
            $song = $em->find('Songbook\Entity\Song', $id);
        } else {
            $song = new \Songbook\Entity\Song();
        }

        $form = new SongEditForm();
        $request = $this->getRequest();

        if( $request->isPost()){

            $data = $request->getPost();
            $form->setData($data[$form->getName()]);

            if($form->isValid())
            {
                $song->exchangeArray($form->getData());
                $em->persist($song);
                $em->flush();
            }


        }
        return array(
          'form' => $form
        );
    }

    public function viewAction ()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        $songService = $this->getSongService();
        $contentService = $this->getContentService();

        try {
            $song = $songService->getSongById($id);
        } catch( \Exception $e){
            throw $e;
            //return $this->notFoundAction();
        }

        $files = $songService->getSongContent($song, Content::TYPE_GDRIVE_CLOUD_FILE);
        $videos = $songService->getSongContent($song, Content::TYPE_LINK);
        //$songService->addSongContent($song, '/tmp/blago', Content::TYPE_GDRIVE_CLOUD_FILE);

        return array(
            'song' => $song,
            'files' => $files,
            'videos' => $videos,
            'songService' => $songService,
            'contentService' => $contentService,
        );
    }

    public function listAction ()
    {
        $em = $this->getEntityManager();
        $user = $em->find('User\Entity\User', 1);
        // take all songs
        $songs = $this->getEntityManager()->getRepository('Songbook\Entity\Song')->findByUserWithHeaders($user, null, array('f.content' => 'ASC', 'h.content' => 'ASC'));

        return array(
          'songs' => $songs
        );
    }

    public function deleteAction ()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (! $id) {
            return $this->redirect()->toRoute('songbook');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = (int) $request->getPost('id');
            $del = $request->getPost('del', 'No');

            $isConfirm = false;

            if ($del == 'Yes') {
                $isConfirm = true;
            }
        } else {
            $isConfirm = true;
        }

        if ($isConfirm) {
            $song = $this->getEntityManager()->find('Songbook\Entity\Song', $id);

            if ($song) {
                $this->getSongService()->deleteSong($song);
            }
        }

        // Redirect to list of songs
        $router = $this->getEvent()->getRouter();
        $url = $router->assemble(
                array(
                    'controller' => 'song',
                    'action' => 'list'
                ), array(
                    'name' => 'default'
                ));

        return $this->redirect()->toUrl($url);
    }

    public function mergeAction()
    {
        // find all songs with titles
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Songbook\Entity\Song');
        $user = $em->find('User\Entity\User', 1);

        $request = $this->getRequest();

        // if song id given in query
        $id = (int) $this->params()->fromRoute('id', 0);

        $master = null;
        $similar = array();

        if ($id) {
            // get this song with favorite and default header
            $master = $repo->findWithHeaders($user, $id );
            /* @var $master Song */
        }

        if ($request->isPost()) {
            if ($master) {
                $array = $_POST['merge'];
                $merge = array();
                foreach ($array as $idMerge => $checked) {
                    if ($checked) {
                        $merge[] = $idMerge;
                    }
                }

                if (count($merge)) {
                    $service = $this->getSongService();
                    /* @var $service Service\Song */
                    $service->mergeSongs($master->id, $merge );
                }

                $router = $this->getEvent()->getRouter();
                $url    = $router->assemble(array('id' => $id, 'controller' => 'song', 'action' => 'merge'), array('name' => 'default'));
                return $this->redirect()->toUrl($url . '#song-master-' . $master->id );
            }
        }

        $similar = array();

        if ($master) {
                // find similar
                $similar = $this->getEntityManager()
                    ->getRepository('Songbook\Entity\Song')
                    ->findSimilarWithHeaders($master, $user, null, array('h.content' => 'ASC'));
        }

        // take all songs
        $songs = $repo->findByUserWithHeaders($user, null, array('t.create_time' => 'DESC'));

        return array(
            'songs' => $songs,
            'master' => $master,
            'similar' => $similar
        );
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager ()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get(
                    'doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

    /**
     * @return \Songbook\Service\Song
     */
    protected function getSongService ()
    {
        if (is_null($this->songService)){
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

    /**
     * @return \Songbook\Service\Content
     */
    protected function getContentService ()
    {
        if (is_null($this->contentService)){
            $sm = $this->getServiceLocator();
            $this->contentService = $sm->get('Songbook\Service\Content');
        }

        return $this->contentService;
    }
}
