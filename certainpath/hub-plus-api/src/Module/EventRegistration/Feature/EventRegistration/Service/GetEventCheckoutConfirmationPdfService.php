<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\EventCheckout;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

final readonly class GetEventCheckoutConfirmationPdfService
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function generatePdfDownload(EventCheckout $checkout): StreamedResponse
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $eventSession = $checkout->getEventSession();
        $eventEntity = $eventSession?->getEvent();
        $venue = $eventSession?->getVenue();
        $timezone = $eventSession?->getTimezone();

        $tzIdentifier = $timezone?->getIdentifier();

        $originalStart = $eventSession?->getStartDate();
        $originalEnd = $eventSession?->getEndDate();

        $startDateInTz = null;
        $endDateInTz = null;
        if ($tzIdentifier && $originalStart) {
            $startDateInTz = new \DateTime($originalStart->format('Y-m-d H:i:s'));
            $startDateInTz->setTimezone(new \DateTimeZone($tzIdentifier));
        } elseif ($originalStart) {
            $startDateInTz = new \DateTime($originalStart->format('Y-m-d H:i:s'));
        }

        if ($tzIdentifier && $originalEnd) {
            $endDateInTz = new \DateTime($originalEnd->format('Y-m-d H:i:s'));
            $endDateInTz->setTimezone(new \DateTimeZone($tzIdentifier));
        } elseif ($originalEnd) {
            $endDateInTz = new \DateTime($originalEnd->format('Y-m-d H:i:s'));
        }

        $html = $this->twig->render('pdf/confirmation.html.twig', [
            'confirmationNumber' => $checkout->getConfirmationNumber(),
            'finalizedAt' => $checkout->getFinalizedAt(),
            'amount' => $checkout->getAmount(),
            'contactName' => $checkout->getContactName(),
            'contactEmail' => $checkout->getContactEmail(),
            'eventName' => $eventEntity?->getEventName(),
            'eventSessionName' => $eventSession?->getName(),
            'startDate' => $startDateInTz->format('m/d/Y g:i A'),
            'endDate' => $endDateInTz->format('m/d/Y g:i A'),
            'isVirtualOnly' => $eventSession?->isVirtualOnly() ?? false,
            'timezoneIdentifier' => $tzIdentifier,
            'timezoneShortName' => $timezone?->getShortName(),
            'venueName' => $venue?->getName(),
            'venueAddress' => $venue?->getAddress(),
            'venueCity' => $venue?->getCity(),
            'venueState' => $venue?->getState(),
            'venuePostalCode' => $venue?->getPostalCode(),
            'venueCountry' => $venue?->getCountry(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        $filename = 'confirmation-'.($checkout->getConfirmationNumber() ?? 'unknown').'.pdf';

        $response = new StreamedResponse(function () use ($pdfOutput) {
            echo $pdfOutput;
        });
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
