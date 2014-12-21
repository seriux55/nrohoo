<?php

namespace Base\NrohoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Base\NrohoBundle\Entity\Product;
use Base\NrohoBundle\Form\Type\ProductType;
use Base\NrohoBundle\Entity\Message;
use Base\NrohoBundle\Form\Type\MessageType;
use Base\NrohoBundle\Entity\Comment;
use Base\NrohoBundle\Form\Type\CommentType;
use Base\NrohoBundle\Entity\Demande;
use Base\NrohoBundle\Entity\Avis;
use Base\NrohoBundle\Form\Type\AvisType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $wilaya = array(
            "01 - Adrar", "02 - Chlef", "03 - Laghouat", "04 - Oum El Bouaghi", "05 - Batna",
            "06 - Bejaia", "07 - Biskra", "08 - Bechar", "09 - Blida", "10 - Bouira", "11 - Tamanrasset",
            "12 - Tebessa", "13 - Tlemcen", "14 - Tiaret", "15 - Tizi Ouzou", "16 - Alger",
            "17 - Djelfa", "18 - Jijel", "19 - Setif", "20 - Saida", "21 - Skikda",
            "22 - Sidi Bel Abbes", "23 - Annaba", "24 - Guelma", "25 - Constantine", "26 - Medea",
            "27 - Mostaganem", "28 - MSila", "29 - Mascara", "30 - Ouargla", "31 - Oran",
            "32 - Bayadh", "33 - Illizi", "34 - Bordj Bou Arreridj", "35 - Boumerdes",
            "36 - El Tarf", "37 - Tindouf", "38 - Tissemsilt", "39 - El Oued", "40 - Khenchela",
            "41 - Souk Ahras", "42 - Tipaza", "43 - Mila", "44 - Ain Defla", "45 - Naama",
            "46 - Ain Temouchent", "47 - Ghardaia", "48 - Relizane"
        );
        
        $row = $this->get('database_connection')->prepare("SELECT depart FROM product");
        $row->execute();
        $ville = array();
        while ($data = $row->fetch()) {
            if(!in_array($data['depart'], $ville)){
		$ville[] = $data['depart'];
            }
        }
        asort($ville);
        
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->addSelect('b')
                   ->leftJoin('a.user', 'b')
                   ->where('a.valid = :valid')
                   ->setParameter('valid', '1')
                   ->orderBy('a.id','DESC')
                   ->setMaxResults(10)
                ;
        $product = $qb->getQuery()->getResult();
        
        return $this->render('BaseNrohoBundle:Default:index.html.twig', array(
            'product' => $product,
            'wilaya'  => $wilaya,
            'ville'   => $ville,
        ));
    }
    
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->find('BaseNrohoBundle:Product', $id)->setValid('2');
        $em->flush();
        return $this->forward('BaseNrohoBundle:Default:annonce');
    }
    
    public function inscriptionAction(Request $request)
    {
        
        $product = new Product();
        $product->setIp($this->getRequest()->getClientIp());
        
        $form = $this->createForm(new ProductType(), $product);
        
        if ($form->handleRequest($request)->isValid()) {
            $product->setUser($this->get('security.context')->getToken()->getUser());
            $product->setIp($this->getRequest()->getClientIp());
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl('nroho_base_default', array('id' => $product->getId())));
        }
            
        return $this->render('BaseNrohoBundle:Default:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function addSelectAction(Request $request, $first, $seconde)
    {
        $wilaya = array(
                    "01 - Adrar", "02 - Chlef", "03 - Laghouat", "04 - Oum El Bouaghi", "05 - Batna",
                    "06 - Bejaia", "07 - Biskra", "08 - Bechar", "09 - Blida", "10 - Bouira", "11 - Tamanrasset",
                    "12 - Tebessa", "13 - Tlemcen", "14 - Tiaret", "15 - Tizi Ouzou", "16 - Alger",
                    "17 - Djelfa", "18 - Jijel", "19 - Setif", "20 - Saida", "21 - Skikda",
                    "22 - Sidi Bel Abbes", "23 - Annaba", "24 - Guelma", "25 - Constantine", "26 - Medea",
                    "27 - Mostaganem", "28 - MSila", "29 - Mascara", "30 - Ouargla", "31 - Oran",
                    "32 - Bayadh", "33 - Illizi", "34 - Bordj Bou Arreridj", "35 - Boumerdes",
                    "36 - El Tarf", "37 - Tindouf", "38 - Tissemsilt", "39 - El Oued", "40 - Khenchela",
                    "41 - Souk Ahras", "42 - Tipaza", "43 - Mila", "44 - Ain Defla", "45 - Naama",
                    "46 - Ain Temouchent", "47 - Ghardaia", "48 - Relizane");
        
        foreach ($wilaya as $key => $value) {
            if(substr($value, 0, 2) == substr($first, 1, 3)){
                $depart = $value;  
            }
            if(substr($value, 0, 2) == substr($seconde, 1, 3)){
                $arrivee = $value;  
            }
        }
        $product = new Product();
        $product->setIp($this->getRequest()->getClientIp());
        $product->setDepart($depart);
        $product->setArrivee($arrivee);
        
        $form = $this->createForm(new ProductType(), $product);
        
        if ($form->handleRequest($request)->isValid()) {
            $product->setUser($this->get('security.context')->getToken()->getUser());
            $product->setIp($this->getRequest()->getClientIp());
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl('nroho_base_default', array('id' => $product->getId())));
        }
            
        return $this->render('BaseNrohoBundle:Default:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $product = $em->getRepository('BaseNrohoBundle:Product')->find($id);

        if (null === $product) {
          throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        $form = $this->createForm(new productType(), $product);
        
        // On récupère la requête
        $request = $this->get('request');
        // On vérifie qu'elle est de type POST
        if ($request->getMethod() == 'POST') {
        
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $article contient les valeurs entrées dans le formulaire par le visiteur
            $form->bind($request);
            
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                // On l'enregistre notre objet $article dans la base de données
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                // On redirige vers la page de visualisation de l'article nouvellement créé
                return $this->redirect($this->generateUrl('nroho_base_product', array('id' => $product->getId())));

            }
        }
        
        return $this->render('BaseNrohoBundle:Default:edit.html.twig', array(
           'form' => $form->createView(),
        ));
    }
    
    public function productAction($id)
    {
        // detail du covoiturage
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.user', 'b')->addSelect('b')
                   ->where("a.id = :id AND a.valid = '1'")
                   ->setParameter('id', $id)
                ;
        $product = $qb->getQuery()->getResult();
        // nombre de places max
        foreach($product as $value) {
            $nbrPlace = $value->getPlace();
            $userDist = $value->getUser();
        }
        // formulaire de reservation
        $reservation = new Demande();
        $reservation->setIp($this->getRequest()->getClientIp());
        $formD = $this->createFormBuilder($reservation)->add('nombre', 'integer', array('attr' => array('min' =>1, 'max' => $nbrPlace)))->getForm();  
        // formulaire de message
        $message = new Message();
        $formM = $this->createForm(new MessageType, $message);
        // --- Gestion du commentaire ---
        $comment = new Comment();
        $comment->setIp($this->getRequest()->getClientIp());
        $form = $this->createForm(new CommentType, $comment);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $comment->setProduct($this->getDoctrine()->getManager()->getRepository('BaseNrohoBundle:Product')->find($id));
                $comment->setUser($this->get('security.context')->getToken()->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                return $this->redirect($this->generateUrl('nroho_base_product', array('id' => $comment->getId())));
            }
            $formD->bind($request);
            if ($formD->isValid()){
                $reservation->setUser($this->get('security.context')->getToken()->getUser());
                $reservation->setProduct($this->getDoctrine()->getManager()->getRepository('BaseNrohoBundle:Product')->find($id));
                $em = $this->getDoctrine()->getManager();
                $em->persist($reservation);
                $em->flush();
                return $this->redirect($this->generateUrl('nroho_base_product', array('id' => $id)));
            }
            $formM->bind($request);
            if ($formM->isValid()){
                $message->setUser($this->get('security.context')->getToken()->getUser());
                $message->setProduct($this->getDoctrine()->getManager()->getRepository('BaseNrohoBundle:Product')->find($id));
                $message->setUserDist($userDist);
                $message->setIp($this->getRequest()->getClientIp());
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
                return $this->redirect($this->generateUrl('nroho_base_product', array('id' => $id)));
            }
        }
        // --- Fin de la gestion du commentaire ---
        // Affichage des commentaires
        $qbb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Comment')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.product', 'b')->addSelect('b')
                   ->leftJoin('a.user', 'c')->addSelect('c')
                   ->where('a.product = :id')
                   ->setParameter('id', $id)
                ;
        $comments = $qbb->getQuery()->getResult();
        // Le nombre de commentaires
        $nbr = $this->getDoctrine()->getRepository('BaseNrohoBundle:Comment')
                    ->createQueryBuilder('a')
                    ->addSelect('COUNT(a) AS nbr')
                    ->where('a.product = :id')
                    ->setParameter('id', $id)
                    ->getQuery()->getResult();
        return $this->render('BaseNrohoBundle:Default:product.html.twig', array(
            'product'  => $product,
            'comments' => $comments,
            'nbr'      => $nbr,
            'form'     => $form->createView(),
            'formD'    => $formD->createView(),
            'formM'    => $formM->createView(),
        ));
    }
    
    public function annonceAction()
    {
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.user', 'b')->addSelect('b')
                   ->where('a.user = :id')
                   ->setParameter('id', $this->get('security.context')->getToken()->getUser())
                   ->setFirstResult(0) //offset
                   ->setMaxResults(2)  //limit
                ;
        $product = $qb->getQuery()->getResult();
        // afficher en session $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
        return $this->render('BaseNrohoBundle:Default:annonce.html.twig', array(
            'product' => $product
        ));
    }
    
    public function rechercheAction()
    {

        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.user', 'b')->addSelect('b')
                   ->where('a.valid = "1"')
                   ->setFirstResult(0) //offset
                   ->setMaxResults(5)  //limit
                ;
        $product = $qb->getQuery()->getResult();
        
        return $this->render('BaseNrohoBundle:Default:recherche.html.twig', array(
            'product' => $product,
        ));
    }
    
    public function profilAction($id)
    {
        $avis = new Avis;
        $avis->setIp($this->getRequest()->getClientIp());
        $form = $this->createForm(new AvisType(), $avis);
        $request = $this->get('request');
        if ($form->handleRequest($request)->isValid()) {
            $avis->setUser($this->get('security.context')->getToken()->getUser());
            $avis->setUserAvis($this->getDoctrine()->getRepository('BaseUserBundle:User')->find($id));
            $em = $this->getDoctrine()->getManager();
            $em->persist($avis);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl('nroho_base_profil', array('id' => $id)));
        }
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Avis')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.user', 'b')->addSelect('b')
                   ->orderBy('a.id','ASC')
                   ->where('a.user_avis = :id')
                   ->setParameter('id', $id)
                ;
        $tout_avis = $qb->getQuery()->getResult();
        $qbb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                    ->createQueryBuilder('a')
                    ->leftJoin('a.user', 'b')->addSelect('b')
                    ->where('b.id = :id')
                    ->setParameter('id', $id)
                    ->setMaxResults(1)
                ;
        $user = $qbb->getQuery()->getResult();
        $qbbb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                     ->createQueryBuilder('a')
                     ->where('a.user = :id')
                     ->orderBy('a.id','DESC')
                     ->setParameter('id', $id)
                     ->setMaxResults(5)
                ;
        $ways = $qbbb->getQuery()->getResult();
        return $this->render('BaseNrohoBundle:Default:profil.html.twig', array(
            'form' => $form->createView(),
            'avis' => $tout_avis,
            'user' => $user,
            'id'   => $id,
            'ways' => $ways,
        ));
    }
    
    public function aideAction()
    {
        return $this->render('BaseNrohoBundle:Default:aide.html.twig');
    }
    
    public function topAction($page = 10)
    {
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->addSelect('b')
                   ->leftJoin('a.user', 'b')
                   ->where('a.valid = :valid')
                   ->setParameter('valid', '1')
                   ->orderBy('a.id','DESC')
                   ->setFirstResult($page)
                   ->setMaxResults(10)
                ;
        $product = $qb->getQuery()->getResult();
        
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($product, 'json');
        return new Response($reports);
    }
    
    public function searchAction($first, $seconde)
    {
        $qb = $this->getDoctrine()->getRepository('BaseNrohoBundle:Product')
                   ->createQueryBuilder('a')
                   ->leftJoin('a.user', 'b')->addSelect('b')
                   ->where("a.depart LIKE :depart AND a.arrivee LIKE :arrivee AND a.valid = '1'")
                   ->setParameter('depart', $first.'%')->setParameter('arrivee', $seconde.'%')
                ;
        $product = $qb->getQuery()->getResult();
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($product, 'json');
        return new Response($reports);
    }
    
    public function destinationAction($start)
    {
        $db  = $this->get('database_connection');
        $row = $db->prepare("SELECT arrivee FROM product WHERE depart LIKE ? AND valid = '1'");
        $row->bindValue(1, $start.'%');
        $row->execute();
        $product = array();
        while ($data = $row->fetch()) {
            if(!in_array($data['arrivee'], $product) && $data['arrivee'] !== ""){
                $product[] = $data['arrivee'];
            }
        }
        sort($product);
        $serializer = $this->container->get('serializer');
        $reports = $serializer->serialize($product, 'json');
        return new Response($reports);
    }
}
