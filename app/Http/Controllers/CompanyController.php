<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    private $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function index(): JsonResponse
    {
        $companies = $this->companyRepository->getAllCompanies();
        return response()->json($companies);
    }

    public function store(CompanyRequest $request): JsonResponse
    {
        $company = $this->companyRepository->create($request->all());
        return response()->json($company, Response::HTTP_CREATED);
    }

    public function show(Company $company): JsonResponse
    {
        $company = $this->companyRepository->show($company);
        return response()->json($company);
    }

    public function update(CompanyRequest $request, Company $company): JsonResponse
    {
        $company = $this->companyRepository->update($request->all(), $company);
        return response()->json($company);
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->companyRepository->delete($company);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
