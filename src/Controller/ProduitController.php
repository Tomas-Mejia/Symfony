<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class ProduitController extends AbstractController
{
    /**
     * @Route("/produits", name="produits")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $fichier = $form->get('photoUpload')->getData();

            if ($fichier) {

                $nomFichier = uniqid() . '.' . $fichier->guessExtension();

                try {
                    $fichier->move(
                        $this->getParameter('upload_dir'),
                        $nomFichier
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible de déplacer le fichier');
                    echo 'Impossible de déplacer le fichier';
                }

                $produit->setPhoto($nomFichier);
            }

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', $translator->trans('Produit crée'));
        }
        $produit = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produit,
            'ajout_produit' => $form->createView()
        ],);
    }

    /**
     * @Route("/produits/edit/{id}", name="produit_details")
     */
    public function edit(Request $request, Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {

            $form = $this->createForm(ProduitType::class, $produit);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $pdo = $this->getDoctrine()->getManager();

                $pdo->persist($produit);  // prepare en PDO
                $pdo->flush();              // execute en PDO

            }

            return $this->render('produit/produit.html.twig', [
                'produit'         => $produit,
                'produit_details'    => $form->createView()
            ]);
        } else {

            $this->addFlash('danger', $translator->trans('Produit introuvable'));
            return $this->redirectToRoute('produits');
        }
    }

    /**
     * @Route("/produits/delete/{id}", name="produit_delete")
     */
    public function delete(Produit $produit = null, TranslatorInterface $translator)
    {

        if ($produit != null) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            $this->addFlash('warning', $translator->trans('Produit supprimé'));
        } else {
            $this->addFlash('danger', $translator->trans('Produit introuvable'));
        }

        return $this->redirectToRoute('produits');
    }


    /**
     * @Route("/", name="add_panier")
     */
    public function additem(Request $request, Produit $produit = null, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', $translator->trans('Panier crée'));
        }
        $panier = $em->getRepository(Panier::class)->findAll();

        return $this->render('panier/index.html.twig', [
            'paniers' => $panier,
            'ajout_panier' => $form->createView()
        ]);
    }
}
