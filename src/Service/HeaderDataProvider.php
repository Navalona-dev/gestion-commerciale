<?php 

namespace App\Service;

use App\Repository\ApplicationRepository;

class HeaderDataProvider
{
    private $applicationRepo;

    public function __construct(
        ApplicationRepository $applicationRepo
    )
    {
        $this->applicationRepo = $applicationRepo;
    }

    public function getHeaderData()
    {
        $applications = $this->applicationRepo->findAllApplication();

        return [
            'applications' => $applications,

        ];
    }


}
