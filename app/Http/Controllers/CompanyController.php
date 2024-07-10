<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Repositories\Bitrix24\CompanyRepository as Bitrix24CompanyRepository;
use App\Repositories\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    private $companyRepository;

    public function __construct(Bitrix24CompanyRepository $companyRepository, /* Bitrix24Repository */)
    {
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display a listing of the resource.
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $companies = $this->companyRepository->getAllCompanies();
        return response()->json($companies);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param CompanyRequest $request
     * @return JsonResponse
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        $company = $this->companyRepository->create($request->all());
        return response()->json($company, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $companyId
     * @return JsonResponse
     */
    public function show(int $companyId): JsonResponse
    {
        $company = $this->companyRepository->show($companyId);
        return response()->json($company);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param CompanyRequest $request
     * @param int $companyId
     * @return JsonResponse
     */
    public function update(CompanyRequest $request, int $companyId): JsonResponse
    {
        $company = $this->companyRepository->update($request->all(), $companyId);
        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $companyId
     * @return JsonResponse
     */
    public function destroy(int $companyId): JsonResponse
    {
        $response = $this->companyRepository->delete($companyId);
        return response()->json($response, Response::HTTP_NO_CONTENT);
    }
}
