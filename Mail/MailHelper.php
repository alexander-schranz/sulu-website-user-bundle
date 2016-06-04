<?php

namespace L91\Sulu\Bundle\WebsiteUserBundle\Mail;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MailHelper implements MailHelperInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        \Swift_Mailer $mailer
    ) {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public function send(
        $from,
        $to,
        $subject,
        $body,
        $replyTo = null,
        $attachments = []
    ) {
        $message = new \Swift_Message(
            $subject,
            $body
        );

        // set from and to
        $message->setFrom($from);
        $message->setTo($to);

        // set attachments
        if (count($attachments) > 0) {
            foreach ($attachments as $file) {
                if ($file instanceof \SplFileInfo) {
                    $path = $file->getPathName();
                    $name = $file->getFileName();
                    // if uploadedfile get original name
                    if ($file instanceof UploadedFile) {
                        $name = $file->getClientOriginalName();
                    }
                    $message->attach(\Swift_Attachment::fromPath($path)->setFilename($name));
                }
            }
        }

        // set replyTo
        if ($replyTo != null) {
            $message->setReplyTo($replyTo);
        }

        return $this->mailer->send($message);
    }
}
