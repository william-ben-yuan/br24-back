<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\Company;
use App\Repositories\ContactRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    private $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function index(Company $company): JsonResponse
    {
        $contacts = $this->contactRepository->getAllContacts($company);
        return response()->json($contacts);
    }

    public function show(Contact $contact): JsonResponse
    {
        return response()->json($contact);
    }

    public function store(ContactRequest $request, Company $company): JsonResponse
    {
        $contact = $this->contactRepository->create($request->all(), $company);
        return response()->json($contact, Response::HTTP_CREATED);
    }

    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        $contact = $this->contactRepository->update($request->all(), $contact);
        return response()->json($contact, Response::HTTP_OK);
    }

    public function destroy(Contact $contact)
    {
        $this->contactRepository->delete($contact);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
