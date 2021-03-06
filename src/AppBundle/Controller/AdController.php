<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ad;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Ad controller.
 *
 * @Route("ad")
 */
class AdController extends Controller
{
    /**
     * Lists all ad entities.
     *
     * @Route("/", name="ad_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $ads = $em->getRepository('AppBundle:Ad')->findAll();

        return $this->render('ad/index.html.twig', array(
            'ads' => $ads,
        ));
    }

    /**
     * Creates a new ad entity.
     *
     * @Route("/new", name="ad_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $ad = new Ad();
        $form = $this->createForm('AppBundle\Form\AdType', $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file=$form['image']->getData();
            $ext=$file->guessExtension();
            $file_name=md5(uniqid()).".".$ext;
            $file->move("uploads", $file_name);
            $ad->setImage($file_name);

            $em = $this->getDoctrine()->getManager();
            $em->persist($ad);
            $em->flush();

            return $this->redirectToRoute('ad_index');
        }

        return $this->render('ad/new.html.twig', array(
            'ad' => $ad,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ad entity.
     *
     * @Route("/{id}", name="ad_show")
     * @Method("GET")
     */
    public function showAction(Ad $ad)
    {
        $deleteForm = $this->createDeleteForm($ad);

        return $this->render('ad/show.html.twig', array(
            'ad' => $ad,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ad entity.
     *
     * @Route("/{id}/edit", name="ad_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Ad $ad)
    {
        $oldImage= $ad->getImage();
        $deleteForm = $this->createDeleteForm($ad);
        $editForm = $this->createForm('AppBundle\Form\AdType', $ad);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

          if (!empty($editForm['image']->getData()))
          {
              unlink('uploads/'.$oldImage);
              $file=$editForm['image']->getData();
              $ext=$file->guessExtension();
              $file_name=md5(uniqid()).".".$ext;
              $file->move("uploads", $file_name);
              $ad->setImage($file_name);
          }else{
              $ad->setImage($oldImage);
          }

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ad_edit', array('id' => $ad->getId()));
        }

        return $this->render('ad/edit.html.twig', array(
            'ad' => $ad,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ad entity.
     *
     * @Route("/{id}", name="ad_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Ad $ad)
    {
        $form = $this->createDeleteForm($ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($ad);
            $em->flush();

            $image=$ad->getImage();
            unlink('uploads/'.$image);

        }

        return $this->redirectToRoute('ad_index');
    }

    /**
     * Creates a form to delete a ad entity.
     *
     * @param Ad $ad The ad entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Ad $ad)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ad_delete', array('id' => $ad->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
