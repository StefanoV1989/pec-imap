<?php

namespace Stefanov1989\PecImap;

interface IPecMessage
{
    public function setPecHeader(IncomingPecHeader $header);
    public function getRiferimentoMessageId(): string|null;
    public function getTipoRicevuta(): string|null;
    public function getRicevuta(): string|null;
    public function getTrasporto(): string|null;
    public function getPecRawBody(): string|null;
    public function getPecSubject():string|null;
    public function getPecAttachments(): array;
    public function getRomeDate(): string;
    public function getPecFromName(): ?string;
    
}
