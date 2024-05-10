<?php

namespace Stefanov1989\PecImap;

use PhpImap\IncomingMail;
use ZBateson\MailMimeParser\MailMimeParser;

class IncomingPec extends IncomingMail implements IPecMessage
{
    private $allegatiIgnore = ['daticert.xml', 'smime.p7s', 'postacert.eml', 'rfc822.eml'];
    private $standardBodyFile = ['rfc822.eml', 'postacert.eml'];
    private $pecRawBody = null;
    public $bodyPecHtml = null;
    public $bodyPecPlain = null;
    public $pecFromAddress = null;
    public $pecFromName = null;
    public $pecSubject = null;
    public $pecMessageId = null;

    public function __construct(IncomingMail $mail, array $allegatiIgnore, array $standardBodyFile)
    {
        foreach (get_object_vars($mail) as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }

        $this->allegatiIgnore = $allegatiIgnore;
        $this->standardBodyFile = $standardBodyFile;

        $this->pecBody();

        $this->pecSubject = $this->getPecSubject();
        $this->pecFromAddress = $this->getPecFrom();
        $this->pecFromName = $this->getPecFromName();

        $pecHeaders = new IncomingPecHeader($this->headers);

        $pecHeaders->ricevuta = $this->getRicevuta();
        $pecHeaders->tipoRicevuta = $this->getTipoRicevuta();
        $pecHeaders->trasporto = $this->getTrasporto();
        $pecHeaders->idMessaggioDiRiferimento = $this->getRiferimentoMessageId();
        $pecHeaders->pecSubject = $this->pecSubject;
        $pecHeaders->pecFromAddress = $this->getPecFrom();
        $pecHeaders->pecMessageId = $this->pecMessageId;
        $pecHeaders->pecFromName = $this->pecFromName;
        

        $this->setPecHeader($pecHeaders);
    }

    public function setPecHeader(IncomingPecHeader $header)
    {
        $this->headers = $header;
    }

    public function getRiferimentoMessageId(): string|null
    {
        $regex = '/X-Riferimento-Message-ID: (<\S+>)/';
        if (preg_match($regex, $this->headersRaw, $match) > 0)
        {
            return $match[1];
        }
        return null;
    }

    public function getTipoRicevuta(): string|null
    {
        $regex = '/X-TipoRicevuta: (completa|breve|sintetica)/';
        if (preg_match($regex, $this->headersRaw, $match) > 0)
        {
            return $match[1];
        }
        return null;
    }

    public function getRicevuta(): string|null
    {
        $regex = '/X-Ricevuta: (non-accettazione|accettazione|preavviso-errore-consegna|presa-in-carico|rilevazione-virus|errore-consegna|avvenuta-consegna)/';
        if (preg_match($regex, $this->headersRaw, $match) > 0)
        {
            return $match[1];
        }
        return null;
    }

    public function getTrasporto(): string|null
    {
        $regex = '/X-Trasporto: (posta-certificata|errore)/';
        if (preg_match($regex, $this->headersRaw, $match) > 0)
        {
            return $match[1];
        }
        return null;
    }

    public function getPecRawBody():string|null
    {
        return $this->pecRawBody;
    }

    public function getPecSubject():string|null
    {
        return str_replace('POSTA CERTIFICATA: ', '', $this->pecSubject);
    }

    private function getPecFrom()
    {
        if ($this->getTrasporto() != null)
        {
            $fromName = $this->fromName;

            preg_match('/Per conto di: ([\w\-.]+@[\w.\-]+)/', $fromName, $match);

            if (count($match) == 2)
            {
                return $match[1];
            }

            return $fromName;
        }
        else
        {
            return $this->fromAddress;
        }
    }

    public function getPecAttachments(): array
    {
        $fileEffettivi = [];
        $elenco = parent::getAttachments();

        foreach($elenco as $allegato)
        {
            if(in_array($allegato->name, $this->allegatiIgnore) ) continue;

            $fileEffettivi[] = $allegato;
        }

        return $fileEffettivi;
    }

    private function pecBody()
    {
        $elenco = parent::getAttachments();
        $body = '';

        // prendo il file .eml
        foreach($elenco as $allegato)
        {
            if(in_array($allegato->name, $this->standardBodyFile))
            {
                $body = $allegato->getContents();
                break;
            }
        }

        if(empty($body))
        {
            $body = $this->headersRaw;
        }

        $this->pecRawBody = $body;
        
        $parser = new MailMimeParser;

        $messaggio = fopen('data://text/plain;base64,' . base64_encode($body), 'r');

        $message = $parser->parse($messaggio, false);

        $this->bodyPecHtml = $message->getHtmlContent();
        $this->bodyPecPlain = $message->getTextContent();

        $this->pecSubject = $message->getSubject();
        $this->pecMessageId = $message->getMessageId();
        $this->pecFromAddress = $message->getHeaderValue('from');
        
    }

    public function getPecFromName():?string
    {
        $body = $this->pecRawBody;

        if($this->getTrasporto())
        {
            $regex = "/From: (.+?) <[^>]+>/i";
            preg_match($regex, $body, $match);

            if(count($match) == 2)
            {
                return $match[1];
            }
            else
            {
                return $this->fromName;
            }
        }
        else
        {
            return $this->fromName;
        }
    }

    public function getRomeDate(): string
    {
        $rome = new \DateTimeZone('Europe/Rome');
        $dateTime = new \DateTime($this->date);
        $dateTime->setTimezone($rome);

        $data_rome = $dateTime->format('Y-m-d H:i:s');

        return $data_rome;
    }
}
