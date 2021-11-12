<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\ImageOptimizer;
use App\SpamChecker;
use Doctrine\ORM\Cache\Exception\NonCacheableEntity;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    private $spamChecker;
    private $entityManager;
    private $commentRepository;
    private $bus;
    private $workflow;
    private $notifier;
    private $imageOptimizer;
    private $photoDir;
    private $logger;

    public function __construct (EntityManagerInterface $entityManager, SpamChecker $spamChecker, CommentRepository $commentRepository, MessageBusInterface $bus, WorkflowInterface $commentStateMachine, NotifierInterface $notifier, ImageOptimizer $imageOptimizer, string $photoDir, LoggerInterface $logger = null)
    {
        $this->entityManager=$entityManager;
        $this->spamChecker=$spamChecker;
        $this->commentRepository=$commentRepository;
        $this->bus=$bus;
        $this->workflow=$commentStateMachine;
        $this->notifier=$notifier;
        $this->imageOptimizer=$imageOptimizer;
        $this->photoDir=$photoDir;
        $this->logger=$logger;
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }
//        }
//        if (2 === $this->spamChecker->getSpamScope ($comment, $message->getContext())) {
//            $comment->setState('spam');
//        } else {
//            $comment->setState('published');
//        }
        if ($this->workflow->can($comment, 'accept')) {
            $scope = $this->spamChecker->getSpamScope($comment, $message->getContext());
            $transition = 'accept';
            if (2 === $scope) {
                $transition = 'reject_spam';
            } elseif (1 === $scope) {
                $transition = 'might_be_spam';
            }
            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
//            $this->workflow->apply($comment, $this->workflow->can($comment, 'publish') ? 'publish' : 'publish_ham');
//            $this->entityManager->flush();
//            $this->mailer->send((new NotificationEmail())
//                ->subject('New comment posted')
//                ->htmlTemplate('emails/comment_notification.html.twig')
//                ->from($this->adminEmail)
//                ->to($this->adminEmail)
//                ->context(['comment' => $comment])
//            );
            $this->notifier->send(new CommentReviewNotification($comment), ...$this->notifier->getAdminRecipients());

            } elseif ($this->workflow->can($comment, 'optimize')) {
            if ($comment->getPhotoFileName()) {
                $this->imageOptimizer->resize($this->photoDir.'/'.$comment->getPhotoFileName());
            }
            $this->workflow->apply($comment, 'optimize');
            $this->entityManager->flush();
            } elseif ($this->logger) {
                $this->logger->debug('Dropping comment message', ['comment'=>$comment->getId(), 'state'=>$comment->getState()]);
        }
    }
}
