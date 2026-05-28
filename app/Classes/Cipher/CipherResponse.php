<?php

declare(strict_types=1);

namespace App\Classes\Cipher;

use App\Exceptions\Cipher\CipherResponseException;
use Illuminate\Http\Client\Response;

class CipherResponse
{
    public function __construct(
        public Response $response {
            get {
                return $this->response;
            }
        }
    ) {
    }

    public function successful(): bool
    {
        return $this->response->successful();
    }

    /**
     * @throws CipherResponseException
     */
    public function throw(): self
    {
        if ($this->response->failed()) {
            throw new CipherResponseException($this->response);
        }

        return $this;
    }

    public function getTicketUuid(): ?string
    {
        return $this->response->json('ticketUuid');
    }

    public function getOwnerFullName(): ?string
    {
        return $this->response->json('signature.certificateInfo.ownerCertificateInfo.value.ownerFullName.value');
    }

    public function getTaxId(): ?string
    {
        return $this->response->json('signature.certificateInfo.extensionsCertificateInfo.value.personalData.value.drfou.value');
    }

    public function getBase64Data(): string
    {
        return $this->response->json('base64Data');
    }
}
