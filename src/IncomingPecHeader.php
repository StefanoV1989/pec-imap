<?php

namespace Stefanov1989\PecImap;

use PhpImap\IncomingMailHeader;

class IncomingPecHeader extends IncomingMailHeader
{
    /** @var string|null */
    public ?string $ricevuta = null;

    /** @var string|null */
    public ?string $tipoRicevuta = null;

    /** @var string|null */
    public ?string $trasporto = null;

    /** @var string|null */
    public ?string $idMessaggioDiRiferimento = null;

    /** @var string|null */
    public ?string $pecFromAddress = null;

    /** @var string|null */
    public ?string $pecFromName = null;
    
    /** @var string|null */
    public ?string $pecSubject = null;

    /** @var string|null */
    public ?string $pecMessageId = null;

    public function __construct($headers)
    {
        foreach (get_object_vars($headers) as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }
    }
}
