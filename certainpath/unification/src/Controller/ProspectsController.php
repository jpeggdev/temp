<?php

namespace App\Controller;

use App\Entity\Prospect;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\QueryManagerException;
use App\Form\BatchType;
use App\Repository\AbstractRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProspectRepository;
use App\Repository\SavedQueryRepository;
use App\Services\QueryManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProspectsController extends AbstractController
{
    /**
     * @throws QueryManagerException
     * @throws CompanyNotFoundException
     */
    #[Route(
        '/app/company/{identifier}/prospects/queryBuilder',
        name: 'app_company_prospects_query_builder',
        methods: ['GET', 'POST']
    )]
    public function buildQuery(
        Request $request,
        CompanyRepository $companyRepository,
        SavedQueryRepository $savedQueryRepository,
        EntityManagerInterface $entityManager,
        QueryManager $queryManager,
        string $identifier
    ): Response {
        $company = $companyRepository->findOneByIdentifier($identifier);
        if (!$company) {
            throw new CompanyNotFoundException(
                'Identifier: ' . $identifier
            );
        }
        $this->validateCompanyAccess($company);

        $form = $this->createForm(BatchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $query = $entityManager->createQueryBuilder()
                ->select('p')
                ->from(Prospect::class, 'p')
                ->join('p.company', 'a')
                ->where('a.identifier = :companyIdentifier')
                ->setParameter('companyIdentifier', $company->getIdentifier())
                ->orderBy('p.id', 'DESC')
                ->setMaxResults($data['numberOfRecords'])
                ->setFirstResult($data['recordStart'])
                ->getQuery()
            ;

            $queryManager->saveQuery(
                $company,
                $query,
                Prospect::class,
                $data['name'],
                $data['description']
            );

            return $this->redirectToRoute(
                'app_company_prospects_query_builder',
                [
                    'identifier' => $company->getIdentifier()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $savedQueryRepository->getSavedQueriesPaginator($company, $offset);

        return $this->render('prospects/queryBuilder.html.twig', [
            'company' => $company,
            'savedQueries' => $paginator,
            'form' => $form,
            'offset' => $offset,
            'previous' => $offset - AbstractRepository::RESULTS_PER_PAGE,
            'next' => min(count($paginator), $offset + AbstractRepository::RESULTS_PER_PAGE),
        ]);
    }

    /**
     * @throws CompanyNotFoundException
     */
    #[Route('/app/company/{identifier}/prospects', name: 'app_company_prospects')]
    public function manageProspects(
        Request $request,
        CompanyRepository $companyRepository,
        ProspectRepository $prospectRepository,
        string $identifier
    ): Response {
        $company = $companyRepository->findOneByIdentifier($identifier);
        if (!$company) {
            throw new CompanyNotFoundException(
                'Identifier: ' . $identifier
            );
        }
        $this->validateCompanyAccess($company);

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $prospectRepository->getProspectsPaginator($company, $offset);

        return $this->render('prospects/index.html.twig', [
            'company' => $company,
            'prospects' => $paginator,
            'offset' => $offset,
            'previous' => $offset - AbstractRepository::RESULTS_PER_PAGE,
            'next' => min(count($paginator), $offset + AbstractRepository::RESULTS_PER_PAGE),
        ]);
    }
}
