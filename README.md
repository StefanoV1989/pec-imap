#README

Questa libreria è stata creata per supportare il download e la lettura delle email di posta certificata italiana.

Il progetto utilizza due librerie:
- https://github.com/barbushin/php-imap : per il download delle email da un server IMAP
- https://github.com/zbateson/mail-mime-parser : per il parsing della mail contenuta nella busta PEC

## Installazione

```bash
$ composer require stefanov1989/pec-imap
```

### Esempio di utilizzo

```php
$mailbox = new PecMailBox(
    imapPath: '{******:993/imap4/ssl}INBOX',
    login: $login,
    password: $password,
    serverEncoding: 'UTF-8',
);

// Utilizzare i criteri di ricerca di imap_search.
// Per filtrare per oggetto bisogna utilizzare il criterio "SUBJECT" iniziando la stringa con "POSTA CERTIFICATA:"
$mailIds = $mailbox->sortMails(SORTARRIVAL, false, 'ALL');

foreach ($mailIds as $mailId)
{
    // Recupero la pec dal server (la seconda variabile indica se impostare la mail come letta o meno)
    $mail = $mailbox->getPec($mailId, false);

    // recupera un array di allegati, scartando i file mime di firma della pec
    $attachments = $mail->getPecAttachments();

    // salvataggio degli allegati
    foreach ($attachments as $attachment)
    {
        file_put_contents("files/" . $attachment->name, $attachment->getContents());
    }

    // oggetto della mail ripulito
    echo "subject: " . $mail->getPecSubject() . PHP_EOL;
    // mittente della mail (vuoto se è una ricevuta)
    echo "mittente: " . $mail->fromName . PHP_EOL;
    // indirizzo del mittente
    echo "address: " . $mail->fromAddress . PHP_EOL;
    // tipo di ricevuta: completa|breve|sintetica
    echo "tipo ricevuta: " . $mail->getTipoRicevuta() . PHP_EOL;
    // ricevuta: null|non-accettazione|accettazione|preavviso-errore-consegna|presa-in-carico|rilevazione-virus|errore-consegna|avvenuta-consegna
    echo "ricevuta: " . $mail->getRicevuta() . PHP_EOL;
    // trasporto: null|posta-certificata
    echo "trasporto: " . $mail->getTrasporto() . PHP_EOL;
    // corpo html della mail
    echo "body html: " . $mail->bodyPecHtml . PHP_EOL;
    // corpo plain della mail
    echo "body: " . $mail->bodyPecPlain . PHP_EOL;
    // message id
    echo "message id: " . $mail->messageId . PHP_EOL;
}

$mailbox->disconnect();

Per tutti gli altri metodi e proprietà consultare la documentazione delle classi.
```