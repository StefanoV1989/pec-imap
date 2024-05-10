<?php

use Stefanov1989\PecImap\PecMailBox;

require __DIR__ . '/../vendor/autoload.php';

$login = '****';
$password = '***';

$mailbox = new PecMailBox(
    imapPath: '{******:993/imap4/ssl}INBOX',
    login: $login,
    password: $password,
    serverEncoding: 'UTF-8',
);

//$mailIds = $mailbox->sortMails(SORTARRIVAL, false, 'ALL');
$mailIds = $mailbox->sortMails(SORTARRIVAL, true, 'SINCE "01-Mar-2024"');

foreach ($mailIds as $mailId)
{

    $mail = $mailbox->getPec($mailId, false);

    $attachments = $mail->getPecAttachments();

    echo "ALLEGATI: ". PHP_EOL;

    foreach ($attachments as $attachment)
    {
        echo $attachment->name . PHP_EOL;
        file_put_contents("files/" . $attachment->name, $attachment->getContents());
    }
    echo PHP_EOL;
    echo "subject: " . $mail->getPecSubject() . PHP_EOL;
    echo "mittente: " . $mail->fromName . PHP_EOL;
    echo "address: " . $mail->fromAddress . PHP_EOL;
    echo "riferimento messaggio id: " . $mail->getRiferimentoMessageId() . PHP_EOL;
    echo "tipo ricevuta: " . $mail->getTipoRicevuta() . PHP_EOL;
    echo "ricevuta: " . $mail->getRicevuta() . PHP_EOL;
    echo "trasporto: " . $mail->getTrasporto() . PHP_EOL;
    echo "body html: " . $mail->bodyPecHtml . PHP_EOL;
    echo "body: " . $mail->bodyPecPlain . PHP_EOL;
    echo "message id: " . $mail->messageId . PHP_EOL;

    echo PHP_EOL . PHP_EOL;
}

$mailbox->disconnect();
