<?php
namespace Songbook\Controller;
use Songbook\Form\SongEditForm;       // <-- Add this import
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

class SongController extends AbstractActionController
{

    /**
     * @var DoctrineORMEntityManager
     */
    protected $em;

    public function getEntityManager ()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get(
                    'doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

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
        $id = $this->params('id', null);
        $form = new SongEditForm();

        return array(
          'form' => $form
        );
    }

    public function viewAction ()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (! $id) {
            return $this->redirect()->toRoute('songbook');
        }

        return array(
            'form' => $this->getEntityManager()->find('Songbook\Entity\Song',
                    $id)
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
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $album = $this->getEntityManager()->find('Songbook\Entity\Song',
                        $id);
                if ($album) {
                    $this->getEntityManager()->remove($album);
                    $this->getEntityManager()->flush();
                }
            }

            // Redirect to list of songs
            return $this->redirect()->toRoute('songbook');
        }

        return array(
            'id' => $id,
            'song' => $this->getEntityManager()->find('Songbook\Entity\Song',
                    $id)
        );
    }
}
