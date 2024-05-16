<?php

namespace Stefanov1989\PecImap;

use PhpImap\Mailbox;

class PecMailBox extends Mailbox
{
    public array $allegatiIgnore = ['daticert.xml', 'smime.p7s', 'postacert.eml', 'rfc822.eml'];
    public array $standardBodyFile = ['rfc822.eml', 'postacert.eml'];

    public function getPec(int $mailId, bool $markAsSeen = true): IncomingPec
    {
        $mail = parent::getMail($mailId, $markAsSeen);

        $pec = new IncomingPec($mail, $this->allegatiIgnore, $this->standardBodyFile);

        return $pec;
    }
}
