<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($panier);
            $em->flush();

            $this->addFlash('success', $translator->trans('Panier crée'));
        }
        $panier = $em->getRepository(Panier::class)->findAll();

        return $this->render('panier/index.html.twig', [
            'paniers' => $panier,
            'ajout_panier' => $form->createView()
        ]);
    }


    /**
     * @Route("/delete/{id}", name="panier_delete")
     */
    public function delete(Panier $panier = null, TranslatorInterface $translator)
    {

        if ($panier != null) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($panier);
            $em->flush();
            $this->addFlash('warning', $translator->trans('Produit supprimé'));
        } else {
            $this->addFlash('danger', $translator->trans('Produit introuvable'));
        }

        return $this->redirectToRoute('home');
    }
}
