<?php
namespace L91\Sulu\Bundle\WebsiteUserBundle\Mail;

interface MailHelperInterface
{
    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $replayTo
     * @param array $attachments
     *
     * @return int
     */
    public function send($from, $to, $subject, $body, $replayTo = null, $attachments = []);
}
