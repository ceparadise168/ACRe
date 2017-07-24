<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\PostType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Entity\Message;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultController extends Controller
{
    /**
     * Get page then redirect list to list/page
     * @Route("page/{page}", name="page")
     * @Method("GET")
     */
    public function pageAction($page)
    {
        $returnArray = ['page'=>$page];

        return $this->redirectToRoute('list', $returnArray);
    }

    /**
     * Get message list and setting page
     * index
     * @Route("/{page}", name="list", requirements={"page": "\d+"})
     */
    public function indexAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('AppBundle:Message');

        $thisPage = $page;
        $limit = 5;
        $offset = $limit * ($page-1);
        $maxPages = ceil(count($messages->findAll()) / $limit);

        $qb = $messages->createQueryBuilder('m')
            ->orderBy('m.updatedAt', 'DESC')
            ->setFirstResult($limit * ($page -1))
            ->setMaxResults($limit);
        $query = $qb->getQuery();
        $result = $query->getResult();

        $messages = $result;
        $renderArray = [
            'messages' => $messages,
            'maxPages' => $maxPages,
            'thisPage' => $thisPage
        ];

        if (isset($messages)) {
            return $this->render('index.html.twig', $renderArray);
        } else {
            return new Response('something worng with messages');
        }
    }

    /**
     * Add New Message
     * @Route("add", name="add")
     */
    public function createAction(Request $request)
    {
        $message = new Message();
        $form = $this->createForm('AppBundle\Form\MessageType', $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()  && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $redirectArray = ['id' => $message->getId()];

            return $this->redirectToRoute('list', $redirectArray);
        }
        $renderArray = [
            'message' => $message,
            'form' => $form->createView()
        ];

        return $this->render('/add.html.twig', $renderArray);
    }

    /**
     * Show the Message
     * @Route("show/{id}", name="show")
     * @Method("GET")
     */
    public function showAction(Message $message)
    {
        $deleteForm = $this-> createDeleteForm($message);
        $renderArray = [
            'message' => $message,
            'delete_form' => $deleteForm->createView()
        ];

        return $this->render('show.html.twig', $renderArray);
    }

    /**
     * Check status by show all Messages
     * @Route("showall", name="showall")
     */
    public function showAllAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Message');
        $Msgs = $repository->findAll();
        $renderArray = ['messages' => $Msgs];

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new ObjectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($renderArray,'json');

        return new Response($json);
    }

    /**
     * Update the Message
     * @Route("edit/{id}", name="edit")
     * @Method({"GET", "POST"})
     */
    public function updateAction(Request $request, Message $message)
    {
        $em = $this->getDoctrine()->getManager();

        $deleteForm = $this->createDeleteForm($message);
        $updateForm = $this->createForm('AppBundle\Form\MessageType', $message);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $message->setupdatedAt(new \DateTime('now', new \DateTimeZone('Asia/Taipei')));
            $em->flush();

            return $this->redirectToRoute('list');
        }

        $renderArray = [
            'message' => $message,
            'edit_form' => $updateForm->createView(),
            'delete_form' => $deleteForm->createView()
        ];

        return $this->render('edit.html.twig', $renderArray);
    }

    /**
     * Delete the Message
     * @Route("delete/{id}", name="delete")
     */
    public function deleteAction(Request $request, Message $message)
    {
        $form = $this->createDeleteForm($message);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($message);
        $em->flush();

        return $this->redirectToRoute('list');
    }

    /**
     * Create a form to delete a message entity
     *
     * @param Message $message The message entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Message $message)
    {
        $returnArray = ['id' => $message->getId()];

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete', $returnArray))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Get message list and setting page with json
     * @Route("/api/list/{page}", name="apiList", requirements={"page": "\d+"})
     */
    public function indexActionAPI($page = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('AppBundle:Message');

        $thisPage = $page;
        $limit = 5;
        $offset = $limit * ($page-1);
        $maxPages = ceil(count($messages->findAll()) / $limit);

        $qb = $messages->createQueryBuilder('m')
            ->orderBy('m.updatedAt', 'DESC')
            ->setFirstResult($limit * ($page -1))
            ->setMaxResults($limit);
        $query = $qb->getQuery();
        $result = $query->getResult();
        $messages = $result;
        $renderArray = [
            'messages' => $messages,
            'maxPages' => $maxPages,
            'thisPage' => $thisPage
        ];

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new ObjectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($renderArray,'json');

        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

            return $response;
    }

    /**
     * Get a request and add new message
     * @Route("api/add", name="apiAdd")
     * @Method("POST")
     */
    public function createActionAPI(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $u = $data['userName'];
        $m = $data['msg'];
        $publishAt = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));

        $message = new Message();
        $message->setUserName($u);
        $message->setMsg($m);
        $message->setPublishedAt($publishAt);

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new ObjectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($message, 'json');

        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        $redirectArray = ['id' => $message->getId()];
        return $response;
    }

    /**
     * Get a request and edit the Message
     * @Route("api/edit/{id}", name="api/edit")
     * @Method("PUT")
     */
    public function editActionAPI(Request $request, $id)
    {
        $paramMsg = $request->request->get('msg');
        $paramName = $request->request->get('userName');
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('AppBundle:Message');
        $message = $messages->find($id);

        if ($message && $paramMsg !== null && $paramName !== null) {
            $message->setUserName($paramName);
            $message->setMsg($paramMsg);
            $message->setupdatedAt(new \DateTime('now', new \DateTimeZone('Asia/Taipei')));
            $encodersArray = [
                new XmlEncoder(),
                new JsonEncoder()
            ];
            $normalizersArray = [new ObjectNormalizer()];
            $encoders = $encodersArray;
            $normalizers = $normalizersArray;
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($message, 'json');
            $dejson = json_decode($json, true);
            $json = $serializer->serialize($dejson, 'json');
            $response = new Response();
            $response->setContent($json);

            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, TRACE, GET, HEAD, POST, PUT');
            $response->headers->set('Access-Control-Allow-Headers', 'X-Header-One,X-Header-Two');
            $em->flush();
            return $response;
        } else {
            return new Response("GG");
        }
    }

    /**
     * Get a request and delete the message
     * @Route("api/delete/{id}", name="deleteAPI")
     * @Method("DELETE")
     */
    public function deleteActionAPI(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('AppBundle:Message');

        if ($message = $messages->find($id)) {
            $encodersArray = [
                new XmlEncoder(),
                new JsonEncoder()
            ];
            $normalizersArray = [new objectNormalizer()];
            $encoders = $encodersArray;;
            $normalizers = $normalizersArray;
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($message, 'json');

            $em->remove($message);
            $em->flush();

            return JsonResponse::create(json_decode($json, true), 200);
        } else {
            return JsonResponse::create("ID NOT FOUND", 404 );
        }
    }
}
