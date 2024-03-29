<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ConferenceController extends AbstractController
{
    private Environment $twig;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;

    public function __construct (Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->twig=$twig;
        $this->entityManager=$entityManager;
        $this->bus=$bus;
    }
    #[Route('/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('homepage_new', ['_locale' => 'en']);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/{_locale<%app.supported_locales%>}/', name: 'homepage_new')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
////        return $this->render('conference/index.html.twig', [
//            'controller_name' => 'ConferenceController',
//        ]);

//        $greet = '';
//        if ($name) {
//            $greet = sprintf("<h1>Hello, %s!</h1>", htmlspecialchars($name));
//        }


//        return new Response(<<<EOF
//<html>
//    <body>
//$greet
//<img src="images/burger_king_po_Ukraine.jpg" width="250" height="250" alt="Топ картинка"/>
//    </body>
//</html>
//EOF
//);
        $response = new Response($this->twig->render('conference/index.html.twig', [
            'conferences'=>$conferenceRepository->findAll(),
        ]));
        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route('/{_locale<%app.supported_locales%>}/conference_header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        $response = new Response($this->twig->render('conference/header.html.twig', [
            'conferences'=>$conferenceRepository->findAll()
        ]));
        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @param Request $request
     * @param Conference $conference
     * @param CommentRepository $commentRepository
     * @param ConferenceRepository $conferenceRepository
     * @param NotifierInterface $notifier
     * @param string $photoDir
     * @return FileException|RedirectResponse|Exception|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    #[Route ('/{_locale<%app.supported_locales%>}conference/{slug}', name: 'conference_slug')]
    public function show (Request $request, Conference $conference, CommentRepository $commentRepository, ConferenceRepository$conferenceRepository, NotifierInterface $notifier, string $photoDir): FileException|RedirectResponse|Exception|Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){
                $comment->setConference($conference);
                if ($photo = $form['photo']->getData()) {
                    $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                    try {
                        $photo->move($photoDir, $filename);
                    } catch (FileException $e) {
                       return $e;
                    }
                    $comment->setPhotoFileName($filename);
                }

                $this->entityManager->persist($comment);

                $context = [
                    'user_ip'=>$request->getClientIp(),
                    'user_agent'=>$request->headers->get('user-agent'),
                    'referrer'=>$request->headers->get('referer'),
                    'permalink'=>$request->getUri(),
                ];

                $this->entityManager->flush();
                $this->bus->dispatch(new CommentMessage($comment->getId(), $context));

                $notifier->send(new Notification('Thank you for your feedback. Your comment will be posted after moderation.', ['browser']));

                return $this->redirectToRoute('conference_slug', ['slug'=>$conference->getSlug()]);
            }

            if ($form->isSubmitted()) {
                $notifier->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
            }

        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return new Response($this->twig->render('conference/show.html.twig', [
            'conferences'=>$conferenceRepository->findAll(),
            'conference'=>$conference,
//               'comments'=>$commentRepository->findBy([
//                   'conference'=>$conference
//               ], [
//                   'createdAt'=>'DESC'
//               ])
            'comments'=>$paginator,
            'previous'=>$offset- CommentRepository::PAGINATOR_PER_PAGE,
            'next'=>min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form'=>$form->createView()

            ]));
    }
}
